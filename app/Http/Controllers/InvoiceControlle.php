<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Invoice;
use App\Models\Order;
use Illuminate\Support\Facades\DB;

class InvoiceController extends Controller {
    public function __construct() {
    }

    public function fetchByDate( Request $request ) {
        $this->validate( $request, [ 'date' => 'required|date' ] );
        // Assuming the 'date' field corresponds to the 'created_at' timestamp
        $date = $request->date;
        $invoices = Invoice::whereDate( 'created_at', $date )->get();
        return response()->json( $invoices );
    }

    public function create( Request $request ) {
        $this->validate( $request, [
            'location_id' => 'required|exists:locations,id',
            'customer_id' => 'required|exists:customers,id',
            'discount' => 'required|integer'
        ] );

        DB::beginTransaction();

        try {
            $amount = Order::completed( $request->location_id )->sum( 'amount' );
    
            $invoice = Invoice::create( [
                'customer_id' => $request->customer_id,
                'amount' => $request->amount,
                'discount' => $request->discount,
            ] );
    
            if ( !$invoice ) {
                throw new \Exception( 'Failed to Store Invoice' );
            }
    
            // Cancel all Pending Orders
            $openOrders = Order::open( $request->location_id )->get();
            foreach ( $openOrders as $order ) {
                $order->cancel();
            }
    
            // Mark Paid all Completed Orders
            $completedOrders = Order::completed( $request->location_id )->get();
            foreach ( $completedOrders as $order ) {
                $order->paid( $invoice->id );
            }

            DB::commit();

            $result = Invoice::with('orders')->find($invoice->id);
            return response()->json($result);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Unable to Store Invoice'], 500);
        }
    }
}