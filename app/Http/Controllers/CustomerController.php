<?php

namespace App\Http\Controllers;
use App\Models\Customer;
use App\Models\Invoice;
use Illuminate\Http\Request;

class CustomerController extends Controller {
    public function fetch() {
        return response()->json( Customer::all() );
    }

    public function find( $mobile ) {
        $customer = Customer::where( 'mobile', $mobile )->first();
        if ( is_null( $customer ) ) {
            return response()->json( [ 'message' => 'No customer found' ], 204 );
        }
        return response()->json( $customer );
    }

    public function create( Request $request ) {
        $this->validate( $request, [ 'title' => 'required|string|max:255', 'mobile' => 'required|unique:customers,mobile|string|max:15' ] );
        $customer = Customer::create( $request->only( [ 'title', 'mobile' ] ) );
        if ( $customer ) {
            return response()->json( $customer, 201 );
        }

        return response()->json( [ 'message'=>'Failed to Save Customer Information' ], 500 );
    }

    public function update( Request $request, $id ) {
        $this->validate( $request, [ 'title' => 'sometimes|required|string|max:255', 'mobile' => 'sometimes|required|string|max:15' ] );
        $customer = Customer::findOrFail( $id );
        $status = $customer->update( $request->only( [ 'title', 'mobile' ] ) );
        if ( $status ) {
            return response()->json( $customer->refresh() );
        }
        return response()->json( [ 'message'=>'Failed to Save Customer Information' ], 500 );
    }
}