<?php 

namespace App\Services;

use App\Models\Invoice;
use Illuminate\Http\Request;
use App\Models\Ledger;
use App\Models\Voucher;
use App\Models\InvoiceTransaction;
use App\Models\StockTransaction;
use App\Models\Contact;
use App\Models\StockUsageTemplate;
use Illuminate\Support\Facades\DB;


class InvoiceService {

    public static function select(Request $request) {
        if ($request->has('id')) {
            return self::getInvoiceById($request->id);
        }

        else if ($request->has('createdAt')) {
            return self::getInvoiceByDate($request->userId, $request->createdAt);
        }

        else if ($request->has('customerId')) {
            return self::getInvoiceByCustomer($request->customerId, $request->month, $request->input('paid', false));
        }

        return response('Invalid Query Parameter', 406);
    }

    public static function getInvoiceById(int $id) {
        $invoice = Invoice::where('id', $id)
        ->with(['customer', 'generalTransactions'])
        ->first();
        return $invoice;
    }

    public static function getInvoiceByDate(int $user, $created_at) {
        $invoices = Invoice::whereDate('created_at', $created_at)
            ->where('user_id', $user)
            ->with(['customer'])->get();

        return $invoices;
    }

    public static function getInvoiceByCustomer(int $customer_id, string $created_at, bool $paid) {
        $invoices = Invoice::where('created_at', 'LIKE', $created_at . '%')
        ->where('paid', $paid);
        
        if ($customer_id > 0) {
            $invoices = $invoices->where('customer_id', $customer_id);
        }
        $invoices = $invoices->with(['customer'])->get();
        return $invoices;
    }

    public static function createNewInvoice(Request $request, int $user_id) {
        DB::beginTransaction();
        
        try{
            
            $invoice = Invoice::create([
                'contact_id' => $request->input('contact_id'),
                'user_id' => $user_id,
                'paid' => $request->boolean('paid'),
                'amount' => $request->input('amount'),
                'kind' => $request->input('kind')
            ]);

            self::createTransactions(
                $invoice->id,
                $request->input('transactions')
            );

            self::createPaymentVoucher(
                $request->input('transactions'), 
                $request->input('contact_id'),
                $invoice->id,
                $user_id
            );

            self::createReceiptVoucher(
                $request->input('paymentMethod'), 
                $invoice->id,
                $invoice->contact_id,
                $invoice->amount, 
                $user_id
            );

            DB::commit();
        } catch(\Exception $e) {
            DB::rollBack();

            return response('Error', 500);
        }


    }

    public static function createTransactions(
        int $invoice_id, 
        array $transactions) {

        $stockTransactions = [];

        for($i=0; $i < count($transactions); $i++) {
            $transactions[$i]['invoice_id'] = $invoice_id;
        }

        InvoiceTransaction::insert($transactions);

    }

    private static function getCustomerLedger($customer_id) {
        $customer = Contact::findOrFail($customer_id);

        if (!is_null($customer->ledger_id)) {
            return $customer->ledger_id;
        }

        $ledger_id = Contact::where('title', 'Walk-in Customer')->first()->ledger_id;
        if (is_null($ledger_id)) {
            $ledger = Ledger::create(['title' => 'Walk-in Customer','kind' => 'RECEIVABLES']);
            Contact::create(['title' => 'Walk-in Customer','address' => 'Ashoknagar', 'ledger_id' => $ledger->id]);
            return $ledger->id;
        }

        return $ledger_id;
    }

    private static function getSalesLedgerId() {
        $sales = Ledger::where('title', 'Sales')->first();
        if (!$sales) {
            $sales = Ledger::create([
                'title' => 'Sales',
                'kind' => 'INCOME'
            ]);
        }

        return $sales->id;

    }

    private static function createPaymentVoucher(
        array $transactions,
        int $customer_id,
        int $invoice_id, 
        int $user_id
    ) 
    {
        $customer_ledger = self::getCustomerLedger($customer_id);
        $sales_ledger_id = self::getSalesLedgerId();
        
        $saleAmount = 0;

        for ($i = 0; $i < count($transactions); $i++) {
            if ($transactions[$i]['item_type'] === 'LEDGER') {
                Voucher::create([
                    'cr' => $transactions[$i]['item_id'],
                    'dr' => $customer_ledger,
                    'narration' => 'Payment Invoice #' . $invoice_id,
                    'amount' => self::getAmount($transactions[$i]),
                    'user_id' => $user_id
                ]);
            } else {
                $saleAmount += self::getAmount($transactions[$i]);
            }
        }

        if ($saleAmount > 0) {
            Voucher::create([
                'cr' => $sales_ledger_id,
                'dr' => $customer_ledger,
                'narration' => 'Sale Invoice #' . $invoice_id,
                'amount' => $saleAmount,
                'user_id' => $user_id
            ]);
        }
    }

    private static function createReceiptVoucher(
        int $paymentMethod,
        int $invoice_id, 
        int $customer_id,
        float $amount,
        int $user_id
    ) 
    {
        if (is_null($paymentMethod)) {
            return;
        }

        $customer = self::getCustomerLedger($customer_id);
        Voucher::create([
            'cr' => $customer->id,
            'dr' => $paymentMethod,
            'narration' => 'Payment Received for Invoice #' . $invoice_id,
            'amount' => $amount,
            'user_id' => $user_id
        ]);

        

        return response()->json(['status' => 'Success']);
    }

    public static function delete(int $invoice_id) {
        InvoiceTransaction::where('invoice_id', $invoice_id)->delete();
        Invoice::find($invoice_id)->delete();
        Voucher::where('narration', 'LIKE', '%' . $invoice_id)->delete();
    }

    private static function getAmount($transaction) {
        return 
            ($transaction['quantity'] * $transaction['rate']) 
            * (1 - $transaction['discount']/100);
    }

    public function __construct(){}
}