<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\InvoiceService;

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

    public function store(Request $request) {


        $this->validate($request, [
            'kind' => 'required|in:sales,purchase',
            'contact_id' => 'required|integer|min:1|exists:contacts,id',
            'location_id' => 'required|integer|min:1|exists:locations,id',
            'amount' => 'required|numeric|min:1',
        ]);

        $this->validate($request, [
            'transactions.*.product_id' => 'required|integer|min:1|exists:products,id',
            'transaction.*.description' => 'string',
            'transaction.*.quantity' => 'required|numberic|min:1',
            'transaction.*.rate' => 'required|numeric|min:1',
            'transaction.*.gst' => 'required|numeric',
            'transaction.*.amount' => 'required|numeric|min:1'
        ]);
        
        $user_id = Auth::user()->id;
        
        $response = InvoiceService::createNewInvoice($request, $user_id);
        return response($response); 
    }

    public function delete(int $id) {
        InvoiceService::delete($id);
        return response()->json(['message' => 'Invoice #' . $id . 'Deleted Successfully']);
    }
}