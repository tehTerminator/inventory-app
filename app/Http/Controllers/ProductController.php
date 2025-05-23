<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\StockTransferInfo;
use Illuminate\Http\Request;
use App\Services\ProductService;
use Illuminate\Support\Facades\Auth;

class ProductController extends Controller
{

    public function indexProducts() {
        return response()->json(Product::all());
    }

    public function createProduct(Request $request)
    {
        $this->validate($request, [
            'title' => 'required|unique:products,title',
            'quantity' => 'decimal:0,2',
            'rate' => 'decimal:0,2|min:1',
            'location_id' => 'numeric|exists:locations,id'
        ]);

        try {
            $product = ProductService::createProduct(
                $request->input('title'),
                $request->input('rate'),
                $request->input('location_id', 0),
                $request->input('quantity', 0)
            );
            return response()->json($product);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Unable to Create new Product. ' . $e->getMessage()], 500);
        }


    }


    public function indexProductByLocation(Request $request) {
        $this->validate($request, [
            'location' => 'integer'
        ]);

        $location = $request->input('locaiton');
        $product = ProductService::getProductsFromLocation($location);
        return response()->json($product);
    }

    public function getProductById($id) {
        $product = Product::findOrFail($id);
        return response()->json($product);
    }

    public function transfer(Request $request)
    {

        $this->validate($request, [
            'product' => 'required|exists:products,id',
            'myLocation' => 'required|exists:locations,id',
            'toLocation' => 'required|exists:locations,id',
            'narration' => 'required|string',
            'quantity' => 'required|decimal:0,2|min:1'
        ]);

        $info = StockTransferInfo::create([
            'product_id' => $request->input('product'),
            'from_location_id' => $request->input('myLocation'),
            'to_location_id' => $request->input('toLocation'),
            'narration' => $request->input('narration'),
            'user_id' => Auth::user()->id,
            'quantity' => $request->input('quantity'),
        ]);

        $product_id = $request->input('product');
        $fromLocation = $request->input('myLocation');
        $toLocation = $request->input('toLocation');
        $quantity = $request->input('quantity');
    
        ProductService::addProduct($product_id, $toLocation, $quantity);
        ProductService::consumeProduct($product_id, $fromLocation, $quantity);

        
        
        return response()->json($info);
    }

    public function productTransferHistory(Request $request) {
        $this->validate($request, [
            'product_id' => 'required|exists:products,id',
            'location_id' => 'required|exists:locations,id',
            'created_at' => 'required'
        ]);

        $product = $request->input('product_id');
        $location = $request->input('location_id');
        $created_at = $request->input('created_at');

        $history = ProductService::getProductTransferHistory($location, $product, $created_at);
        return response()->json($history);
    }

}
