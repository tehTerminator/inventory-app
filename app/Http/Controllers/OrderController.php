<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller {
    public function __contruct() {
    }

    public function fetchOpen( Request $request ) {
        $location_id = $request->input( 'location_id', 0 );
        return response()->json( Order::open( $location_id )->get() );
    }

    public function fetchCompleted( Request $request ) {
        $location_id = $request->input( 'location_id', 0 );
        return response()->json( Order::completed( $location_id )->get() );
    }

    public function create( Request $request ) {
        $this->validate( $request, [
            'location_id' => [ 'required', 'numeric', 'exists:locations,id' ],
            'product_id' => [ 'required', 'numeric', 'exists:products,id' ],
            'quantity' => [ 'required', 'min:1' ],
            'rate' => [ 'required', 'min:1' ],
        ] );

        $order = Order::create( $request->only( [ 'location_id', 'product_id', 'quantity', 'rate' ] ) );

        if ($order) {
            return response()->json($order);
        }
        return response()->json(['messaage' => 'Failed to Create Order'], 500);
    }

    public function updateStatus(Request $request) {
        $this->validate($request, [
            'id' => ['required', 'numeric', 'exists:orders,id'],
            'status' => ['required', 'in:ACCEPTED,COMPLETE,PAID,CANCELLED']
        ]); 
        $order = Order::findOrFail($request->id);
        switch ($request->status) {
            case 'ACCEPTED':
                $order = $order->accept();
                break;
            case 'COMPLETE':
                $order = $order->complete();
                break;
            case 'PAID':
                $order = $order->paid();
                break;
            case 'CANCELLED':
                $order = $order->cancel();
            default:
                return response()->json(['message' => 'Unspecified Status Provided']);
                break;
        }

        if ($order) {
            return response()->json($order);
        }
        return response()->json(['message' => 'Failed to Change Order Status']);
    }
}

