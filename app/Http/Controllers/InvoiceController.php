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
    public function __construct()
    {
    }

    public function select(Request $request)
    {
        $data = InvoiceService::select($request);
        return response()->json($data);
    }

    public function getById($id)
    {
        $data = InvoiceService::getInvoiceById($id);
        return response()->json($data);
    }

    public function store(Request $request)
    {


        $this->validate($request, [
            'invoice.kind' => 'required|in:SALES,PURCHASE',
            'invoice.contact_id' => 'required|integer|min:1|exists:contacts,id',
            'invoice.location_id' => 'required|integer|min:1|exists:locations,id',
            'invoice.gross_amount' => 'required|decimal:0,2|min:1',
            'invoice.discount_amount' => 'min:0|decimal:0,2'
        ]);

        $this->validate($request, [
            'invoice.transactions.*.product_id' => 'required|exists:products,id',
            'invoice.transactions.*.quantity' => 'required|decimal:0,2|min:0.01',
            'invoice.transactions.*.rate' => 'required|decimal:0,2|min:0.01',
        ]);

        $this->validate($request, [
            'vouchers.*.cr' => 'required|exists:ledgers,id',
            'vouchers.*.dr' => 'required|exists:ledgers,id',
            'vouchers.*.amount' => 'required|decimal:0,2|min:1',
        ]);

        $user_id = Auth::user()->id;

        $response = InvoiceService::createNewInvoice($request, $user_id);
        return response()->json($response);
    }

    public function delete(int $id)
    {
        $response = InvoiceService::delete($id);
        // return response()->json(['message' => 'Invoice #' . $id . ' Deleted Successfully']);
        return response()->json($response);
    }
}
