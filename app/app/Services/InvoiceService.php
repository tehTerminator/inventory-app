<?php

namespace App\Services;

use App\Models\Invoice;
use Illuminate\Http\Request;
use App\Models\Ledger;
use App\Models\Voucher;
use App\Models\InvoiceTransaction;
use App\Models\Contact;
use Illuminate\Support\Facades\DB;
use App\Models\StockTransferInfo;
use App\Services\ProductService;
use App\Models\Bundle;
use App\Models\InvoicePaymentInfo;
use Carbon\Carbon;

class InvoiceService
{
    public static function select(Request $request)
    {
        if ($request->has('id')) {
            return self::getInvoiceById($request->id);
        } else if ($request->has('createdAt')) {
            return self::getInvoiceByDate($request->userId, $request->createdAt);
        } else if ($request->has('contactId')) {
            return self::getInvoiceByCustomer($request->contactId, $request->month, $request->input('paid', false));
        }

        return response('Invalid Query Parameter', 406);
    }

    public static function getInvoiceById(int $id)
    {
        $invoice = Invoice::where('id', $id)
            ->with(['transactions' => function ($query) {
                $query->where('is_child', 0);
            }])
            ->first();

        $invoicePaymentInfos = InvoicePaymentInfo::where('invoice_id', $invoice->id)->get();
        $voucherIds = $invoicePaymentInfos->pluck('voucher_id')->toArray();
        $vouchers = Voucher::whereIn('id', $voucherIds)->get();

        return ['invoice' => $invoice, 'vouchers' => $vouchers];
    }

    public static function getInvoiceByDate(int $user, $created_at)
    {
        $invoices = Invoice::whereDate('created_at', $created_at)
            ->where('user_id', $user)
            ->with(['contact'])->get();

        return $invoices;
    }

    public static function getInvoiceByCustomer(int $customer_id, string $created_at, bool $paid)
    {
        $invoices = Invoice::where('created_at', 'LIKE', $created_at . '%')
            ->where('paid', $paid);

        if ($customer_id > 0) {
            $invoices = $invoices->where('customer_id', $customer_id);
        }
        $invoices = $invoices->with(['contact'])->get();
        return $invoices;
    }

    public static function createNewInvoice(Request $request, int $user_id)
    {

        $message = [];
        DB::beginTransaction();

        try {

            $invoice_data = $request->input('invoice');

            $invoice = Invoice::create([
                'kind' => $invoice_data['kind'],
                'contact_id' => $invoice_data['contact_id'],
                'location_id' => $invoice_data['location_id'],
                'paid' => false,
                'amount' => $invoice_data['amount'],
                'user_id' => $user_id,
            ]);

            if ($invoice) {
                array_push($message, 'Invoice Created With Invoice Id #' . $invoice->id);
            } else {
                throw new \Exception('Unable to Create Invoice');
            }


            $t = self::createTransactions(
                $invoice,
                $invoice_data['transactions']
            );

            array_push($message, $t);


            if ($invoice->kind === 'SALES') {
                $sv = self::createSalesVoucher(
                    $invoice->contact_id,
                    $invoice->id,
                    $user_id
                );
                array_push($message, $sv);
            } else {
                $pv = self::createPurchaseVoucher(
                    $invoice->contact_id,
                    $invoice->id,
                    $user_id
                );
                array_push($message, $pv);
            }

            $cv = self::createVouchers($request->input('vouchers'), $invoice);
            array_push($message, 'Created Vouchers');
            array_push($message, $cv);

            DB::commit();
            return $invoice;
        } catch (\Exception $e) {
            DB::rollBack();
            array_push($message, $e->getMessage());
            return $message;
        }
    }

    private static function createTransactions(
        Invoice $invoice,
        array $transactions
    ) {
        try {
            $contact = Contact::findOrFail($invoice->contact_id);
            $ledgerOfContact = $contact->ledger_id;
            foreach ($transactions as $transaction) {
                self::storeTransaction($transaction, $invoice, $ledgerOfContact);
            }

            return 'Successfully Created Transactions';
        } catch (\Exception $e) {
            return 'Failed to Create Transaction' . $e->getMessage();
        }
    }

    private static function storeTransaction(
        array $transaction,
        Invoice $invoice,
        int $dr_ledger,
        $is_child = false,
        $narration = NULL
    ) {
        $t = InvoiceTransaction::create([
            'quantity' => $transaction['quantity'],
            'rate' => $transaction['rate'],
            'discount' => $transaction['discount'],
            'item_id' => $transaction['item_id'],
            'item_type' => $transaction['item_type'],
            'user_id' => $invoice->user_id,
            'invoice_id' => $invoice->id,
            'is_child' => $is_child
        ]);

        if ($t->item_type === 'BUNDLE') {
            $narration = Bundle::find($t->item_id)->title . ' payment Invoice #' . $t->invoice_id;
            $childTransactions = $transaction['transactions'];
            foreach ($childTransactions as $childTransaction) {
                self::storeTransaction($childTransaction, $invoice, $dr_ledger, true, $narration);
            }
        }

        if ($t->item_type === 'LEDGER') {
            self::createPaymentVoucher($t, $dr_ledger, $narration);
        }

        if ($t->item_type === 'PRODUCT') {
            self::createInventoryTransaction(
                $t,
                $invoice
            );
        }
    }

    private static function createInventoryTransaction(
        InvoiceTransaction $transaction,
        Invoice $invoice
    ) {
        $fromLocation = NULL;
        $toLocation = $invoice->location_id;
        $kind = $invoice->kind;
        $narration = 'Purchase Invoice #' . $transaction['invoice_id'];

        if ($kind === 'SALES') {
            $fromLocation = $invoice->location_id;
            $toLocation = NULL;
            $narration = 'Sales Invoice #' . $transaction['invoice_id'];
        }

        $response = [];

        $info = StockTransferInfo::create([
            'product_id' => $transaction->item_id,
            'from_location_id' => $fromLocation,
            'to_location_id' => $toLocation,
            'narration' => $narration,
            'quantity' => $transaction->quantity,
            'user_id' => $transaction->user_id
        ]);
        try {
            if ($kind === 'sales') {
                ProductService::consumeProduct(
                    $info->product_id,
                    $fromLocation,
                    $transaction['quantity']
                );
            } else {
                ProductService::addProduct(
                    $info->product_id,
                    $toLocation,
                    $info->quantity
                );
            }
            array_push($response, $info);
        } catch (\Exception $e) {
            return $e->getMessage();
        }

        return $response;
    }

    private static function createPaymentVoucher(InvoiceTransaction $t, int $dr_ledger_id, $narration = NULL)
    {
        if (is_null($narration)) {
            $narration = 'Payment Invoice #' . $t->invoice_id;
        }
        Voucher::create([
            'cr' => $t->item_id,
            'dr' => $dr_ledger_id,
            'narration' => $narration,
            'amount' => InvoiceService::calcNetAmount($t->quantity, $t->rate, $t->discount),
            'user_id' => $t->user_id,
            'immutable' => true
        ]);
    }

    public static function createSalesVoucher(
        int $contact_id,
        int $invoice_id,
        int $user_id
    ) {

        $transactions = InvoiceTransaction::where('item_type', 'PRODUCT')->where('invoice_id', $invoice_id)
            ->get();
        $amount = 0;
        foreach ($transactions as $t) {
            $amount += self::calcNetAmount($t->quantity, $t->rate, $t->discount);
        }
        $v = Voucher::create([
            'cr' => self::getSalesLedgerId(),
            'dr' => self::getCustomerLedger($contact_id),
            'narration' => 'Sales Invoice #' . $invoice_id,
            'amount' => $amount,
            'immutable' => true,
            'user_id' => $user_id
        ]);

        if ($v) {
            return 'Successfully Created Sales Voucher #' . $v->id;
        } else {
            return 'Failed to Create A Sales Voucher';
        }
    }

    public static function createPurchaseVoucher(
        int $contact_id,
        int $invoice_id,
        int $user_id
    ) {
        $transactions = InvoiceTransaction::where('item_type', 'PRODUCT')->where('invoice_id', $invoice_id)
            ->get();
        $amount = 0;
        foreach ($transactions as $t) {
            $amount += self::calcNetAmount($t->quantity, $t->rate, $t->discount);
        }

        $v = Voucher::create([
            'cr' => self::getSupplierLedger($contact_id),
            'dr' => self::getPurchaseLedgerId(),
            'narration' => 'Purchase Invoice #' . $invoice_id,
            'amount' => $amount,
            'immutable' => true,
            'user_id' => $user_id
        ]);

        if ($v) {
            return 'Successfully Created Purchase Voucher #' . $v->id;
        } else {
            return 'Failed to Create A Purchase Voucher';
        }
    }

    private static function createVouchers(array $vouchers, Invoice $invoice)
    {
        try {
            $narration = 'Receipt for Sales Invoice #' . $invoice->id;

            if ($invoice->kind === 'PURCHASE') {
                $narration = 'Payment for Purchase Invoice #' . $invoice->id;
            }

            $amount = 0;

            if (is_null($vouchers)) {
                return '$vouchers is NULL';
            }

            foreach ($vouchers as $voucher) {
                $v = Voucher::create([
                    'cr' => $voucher['cr'],
                    'dr' => $voucher['dr'],
                    'narration' => $narration,
                    'amount' => $voucher['amount'],
                    'user_id' => $invoice->user_id,
                    'immutable' => true
                ]);

                $amount += $v->amount;

                InvoicePaymentInfo::create([
                    'invoice_id' => $invoice->id,
                    'contact_id' => $invoice->contact_id,
                    'voucher_id' => $v->id,
                    'amount' => $v->amount
                ]);
            }

            if ($amount >= $invoice->amount) {
                $invoice->paid = true;
                $invoice->save();
                $invoice->refresh();
            }

            return 'Successfully Created Vouchers';
        } catch (\Exception $e) {
            return 'Failed to Create Vouchers ' . $e->getMessage();
        }
    }

    private static function getCustomerLedger($customer_id)
    {
        $contact = Contact::findOrFail($customer_id);

        if (!is_null($contact->ledger_id)) {
            return $contact->ledger_id;
        }

        $ledger = Ledger::where('title', 'Walk-in Customer',)->get();
        if (empty($ledger_id)) {
            $ledger = Ledger::create(['title' => 'Walk-in Customer', 'kind' => 'RECEIVABLE']);
            return $ledger->id;
        }

        return $ledger->id;
    }

    private static function getSupplierLedger($customer_id)
    {
        $contact = Contact::findOrFail($customer_id);

        if (!is_null($contact->ledger_id)) {
            return $contact->ledger_id;
        }

        $ledger = Ledger::where('title', 'Supplier',)->get();
        if (empty($ledger_id)) {
            $ledger = Ledger::create(['title' => 'Supplier', 'kind' => 'PAYABLE']);
            return $ledger->id;
        }

        return $ledger->id;
    }

    private static function getSalesLedgerId()
    {
        $sales = Ledger::where('title', 'Sales Account')->first();
        if (!$sales) {
            $sales = Ledger::create([
                'title' => 'Sales Account',
                'kind' => 'SALES AC'
            ]);
        }

        return $sales->id;
    }

    private static function getPurchaseLedgerId()
    {
        $purchase = Ledger::where('title', 'Purchase Account')->first();
        if (!$purchase) {
            $purchase = Ledger::create([
                'title' => 'Purchase Account',
                'kind' => 'PURCHASE AC'
            ]);
        }

        return $purchase->id;
    }



    private static function calcNetAmount(int $quantity, int $rate, int $discount)
    {
        return ($quantity * $rate) * (1 - $discount / 100);
    }

    public static function delete(int $invoice_id)
    {
        $today = Carbon::today(); // Get today's date object

        $invoice = Invoice::where('created_at', '>=', $today->startOfDay())
            ->where('created_at', '<=', $today->endOfDay())
            ->find($invoice_id);
        
        if(empty($invoice)) {
            return;
        }

        $stockTransactions = StockTransferInfo::where('narration', 'like', '#' . $invoice->id)
            ->get();
        foreach ($stockTransactions as $row) {
            StockTransferInfo::create([
                'product_id' => $row->product_id,
                'from_location_id' => $row->to_location_id,
                'to_location_id' => $row->from_location_id,
                'narration' => 'Deleted Invoice #' . $invoice->id,
                'quantity' => $row->quantity,
                'user_id' => $row->user_id,
            ]);
        }

        InvoiceTransaction::where('invoice_id', $invoice_id)->delete();
        Invoice::find($invoice_id)->delete();
        Voucher::where('narration', 'LIKE', '%' . $invoice_id)->delete();
    }
}
