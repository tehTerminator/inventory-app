<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\InvoiceService;
use App\Models\Invoice;

class InvoiceController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(){ }

    public function select(Request $request) {
       $data = InvoiceService::select($request);
       return response()->json($data);
    }

    public function getById($id) {
        $data = InvoiceService::getInvoiceById($id);
        return response()->json($data);
    }

    public function store(Request $request) {


        $this->validate($request, [
            'invoice.kind' => 'required|in:SALES,PURCHASE',
            'invoice.contact_id' => 'required|integer|min:1|exists:contacts,id',
            'invoice.location_id' => 'required|integer|min:1|exists:locations,id',
            'invoice.amount' => 'required|numeric|min:1',
        ]);

        $this->validate($request, [
            'invoice.transactions.*.item_id' => 'required|integer|min:1',
            'invoice.transactions.*.item_type' => 'string|in:PRODUCT,LEDGER,BUNDLE',
            'invoice.transactions.*.quantity' => 'required|numeric|min:1',
            'invoice.transactions.*.rate' => 'required|numeric|min:1',
            'invoice.transactions.*.discount' => 'required|numeric',
        ]);

        $this->validate($request, [
            'vouchers.*.cr' => 'required|integer|min:1|exists:ledgers,id',
            'vouchers.*.dr' => 'required|integer|min:1|exists:ledgers,id',
            'vouchers.*.amount' => 'required|numeric|min:1',
        ]);
        
        $user_id = Auth::user()->id;
        
        $response = InvoiceService::createNewInvoice($request, $user_id);
        if($response->id) {
            // return response($response);
            $invoice = Invoice::where('id', $response->id)->with(['transactions' => function ($query) {
                $query->where('is_child', false);
            }])->first();
            return response()->json($invoice);
        } else {
            return response($response);
        }
    }

    public function delete(int $id) {
        InvoiceService::delete($id);
        return response()->json(['message' => 'Invoice #' . $id . 'Deleted Successfully']);
    }
}
