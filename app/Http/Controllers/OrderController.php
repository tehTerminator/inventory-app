<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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

    public function getOrderSummary( Request $request ) {
        $this->validate( $request, [
            'location_id' => 'required|exists:locations,id'
        ] );
        $location = $request->input( 'location_id' );

        $currentOrders = Order::currentOrders( $location )->get();
        $totalAmount = 0;
        foreach ( $currentOrders as $order ) {
            $totalAmount += $order->amount;
        }

        $result = [
            'total' => Order::currentOrders( $location )->count(),
            'open' => Order::open( $location )->count(),
            'amount' => $totalAmount
        ];

        return response()->json( $result );
    }

    public function create( Request $request ) {
        $this->validate( $request, [
            'location_id.*' => [ 'required', 'numeric', 'exists:locations,id' ],
            'product_id.*' => [ 'required', 'numeric', 'exists:products,id' ],
            'quantity.*' => [ 'required', 'min:1' ],
            'rate.*' => [ 'required', 'min:1' ],
            'comments.*' => [ 'text' ]
        ] );

        $data = $request->all();
        DB::beginTransaction();
        try {
            foreach ( $data as $item ) {
                Order::create(
                    [
                        'location_id' => $item[ 'location_id' ],
                        'product_id' => $item[ 'product_id' ],
                        'quantity' => $item[ 'quantity' ],
                        'rate' => $item[ 'rate' ],
                        'comments' => $item[ 'comments' ]
                    ] );
                }
                DB::commit();
                return response()->json( [ 'message' => 'Successfully Created all Orders', ], 200 );
            } catch ( \Exception $e ) {
                DB::rollBack();
                return response()->json( [ 'message' => $e->getMessage(), 'data' => $request->all() ], 500 );
            }
        }

        public function updateStatus( Request $request ) {
            $this->validate( $request, [
                'id' => [ 'required', 'numeric', 'exists:orders,id' ],
                'status' => [ 'required', 'in:ACCEPTED,COMPLETE,PAID,CANCELLED' ]
            ] );

            $order = Order::findOrFail( $request->id );
            $statusChanged = false;
            switch ( $request->status ) {
                case 'ACCEPTED':
                $statusChanged = $order->accept();
                break;
                case 'COMPLETE':
                $statusChanged = $order->complete();
                break;
                case 'PAID':
                $statusChanged = $order->paid();
                break;
                case 'CANCELLED':
                $statusChanged = $order->cancel();
                default:
                return response()->json( [ 'message' => 'Unspecified Status Provided' ] );
                break;
            }

            if ( $statusChanged ) {
                return response()->json( $order );
            }
            return response()->json( [ 'message' => 'Failed to Change Order Status' ] );
        }
    }

