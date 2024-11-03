<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;

class ProductsController extends Controller
 {
    public function __construct() {
    }

    public function fetch() {
        return response()->json( Product::all() );
    }

    public function create( Request $request )
 {
        $this->validate( $request, [
            'title' => [ 'required', 'unique:products,title', 'string' ],
            'rate' => [ 'required', 'numeric', 'min:1' ],
            'image_url' => 'string'
        ] );

        $product = Product::create( $request->only( [ 'title', 'rate', 'image_url' ] ) );

        if ( $product ) {
            return response()->json( $product );
        }

        return response()->json( [ 'message' => 'Failed to Store New Product' ], 500 );
    }

    public function update( Request $request )
 {
        $this->validate( $request, [ 'id' => [ 'required', 'numeric', 'exists:products,id' ], 'title' => [ 'required', 'string', 'unique:products,title,' . $request->id ], 'rate' => [ 'required', 'numeric', 'min:1' ], 'image_url' => [ 'nullable', 'string' ] ] );
        $product = Product::findOrFail( $request->id );
        $product->title = $request->title;
        $product->rate = $request->rate;
        $product->image_url = $request->input( 'image_url', $product->image_url );
        $result = $product->save();

        if ( $result ) {
            return response()->json( $product->refresh() );
        }
        return response()->json( [ 'message' => 'Failed to Update Product' ], 500 );
    }

    public function delete( Request $request ) {
        $this->validate( $request, [ 'id' => 'required|exists:products,id' ] );

        $product = Product::findOrFail( $request->id );
        if ( $product->delete() ) {
            return response()->json( [ 'message' => 'Product ' . $product->title . ' Deleted Successfully' ] );
        }
        return response()->json( [ 'message' => 'Unable to Delete Product ' . $product->title ], 500 );
    }

    public function uploadImage( Request $request ) {
        // Validate the incoming request 
        $this->validate( $request, [ 'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048', ] );
        // Get the uploaded file 
        $file = $request->file( 'image' );
        // Generate a random filename 
        $filename = uniqid() . '.' . $file->getClientOriginalExtension();
        // Store the file in the 'public/images' directory 
        $path = $file->storeAs( 'public/images', $filename );
        // Get the URL of the uploaded image 
        $url = Storage::url( $path );
        // Return the URL return 
        response()->json( [ 'url' => $url ] );
    }
