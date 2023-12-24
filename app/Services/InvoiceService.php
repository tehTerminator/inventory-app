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
            ->with(['contact', 'transactions'])
            ->first();
        return $invoice;
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


        DB::beginTransaction();

        try {

            $kind = $request->input('kind');

            $invoice = Invoice::create([
                'kind' => $request->input('kind'),
                'contact_id' => $request->input('contact_id'),
                'location_id' => $request->input('location_id'),
                'paid' => $request->boolean('paid', false),
                'amount' => $request->input('amount'),
                'user_id' => $user_id,
            ]);


            $transactions = self::createTransactions(
                $invoice->id,
                $user_id,
                $request->input('transactions')
            );


            if ($kind === 'sales') {
                self::createSalesVoucher(
                    $request->input('amount'),
                    $request->input('contact_id'),
                    $invoice->id,
                    $user_id
                );
            } else {
                self::createPurchaseVoucher(
                    $request->input('amount'),
                    $request->input('contact_id'),
                    $invoice->id,
                    $user_id
                );
            }

            $inventoryT = self::createInventoryTransactions($transactions, (int)$invoice->location_id, $invoice->kind);

            DB::commit();
            return $invoice;
        } catch (\Exception $e) {
            DB::rollBack();
            return $e->getMessage();
        }
    }

    public static function createSalesVoucher(
        int $amount,
        int $contact_id,
        int $invoice_id,
        int $user_id
    ) {
        Voucher::create([
            'cr' => self::getSalesLedgerId(),
            'dr' => self::getCustomerLedger($contact_id),
            'narration' => 'Sales Invoice #' . $invoice_id,
            'amount' => $amount,
            'immutable' => true,
            'user_id' => $user_id
        ]);
    }

    public static function createPurchaseVoucher(
        int $amount,
        int $contact_id,
        int $invoice_id,
        int $user_id
    ) {
        Voucher::create([
            'cr' => self::getSupplierLedger($contact_id),
            'dr' => self::getPurchaseLedgerId(),
            'narration' => 'Purchase Invoice #' . $invoice_id,
            'amount' => $amount,
            'immutable' => true,
            'user_id' => $user_id
        ]);
    }

    public static function createTransactions(
        int $invoice_id,
        int $user_id,
        array $transactions
    ) {

        $insertedTransactions = [];

        for ($i = 0; $i < count($transactions); $i++) {
            $t = InvoiceTransaction::create([
                'quantity' => $transactions[$i]['quantity'],
                'rate' => $transactions[$i]['rate'],
                'gst' => $transactions[$i]['gst'],
                'amount' => $transactions[$i]['amount'],
                'product_id' => $transactions[$i]['product_id'],
                'user_id' => $user_id,
                'invoice_id' => $invoice_id
            ]);
            array_push($insertedTransactions, $t);
        }

        return $insertedTransactions;
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
        $sales = Ledger::where('title', 'Sales Ledger')->first();
        if (!$sales) {
            $sales = Ledger::create([
                'title' => 'Sales',
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

    private static function createInventoryTransactions(
        array $transactions,
        int $location_id,
        string $kind,
    ) {
        $fromLocation = NULL;
        $toLocation = $location_id;
        $narration = 'Purchase Invoice #' . $transactions[0]['invoice_id'];

        if ($kind === 'sales') {
            $fromLocation = $location_id;
            $toLocation = NULL;
            $narration = 'Sales Invoice #' . $transactions[0]['invoice_id'];
        }

        $response = [];

        for ($i = 0; $i < count($transactions); $i++) {
            $info = StockTransferInfo::create([
                'product_id' => $transactions[$i]->product_id,
                'from_location_id' => $fromLocation,
                'to_location_id' => $toLocation,
                'narration' => $narration,
                'quantity' => $transactions[$i]->quantity,
                'user_id' => $transactions[$i]->user_id
            ]);

            try {
                if ($kind === 'sales') {
                    ProductService::consumeProduct(
                        $info->product_id,
                        $fromLocation,
                        $transactions[$i]['quantity']
                    );
                } 
                else 
                {
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
        }


        return $response;
    }

    public static function delete(int $invoice_id)
    {
        InvoiceTransaction::where('invoice_id', $invoice_id)->delete();
        Invoice::find($invoice_id)->delete();
        Voucher::where('narration', 'LIKE', '%' . $invoice_id)->delete();
    }
}
