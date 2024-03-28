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

    public function createProduct(Request $request)
    {
        $this->validate($request, [
            'title' => 'required|unique:products,title',
            'quantity' => 'numeric',
            'rate' => 'numeric|min:1',
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
            'location' => 'numeric'
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
            'quantity' => 'required|numeric|min:1'
        ]);

        $info = StockTransferInfo::create([
            'product_id' => $request->input('product'),
            'from_location_id' => $request->input('myLocation'),
            'to_location_id' => $request->input('toLocation'),
            'narration' => $request->input('narration'),
            'user_id' => Auth::user()->id,
            'quantity' => $request->input('quantity'),
        ]);
        
        return response()->json($info);
    }
}
