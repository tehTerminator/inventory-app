<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Product;

class ProductsController extends Controller
{
    public function __construct() {}

    public function fetch() {
        return response()->json(Product::all());
    }

    public function create(Request $request)
    {
        $this->validate($request, [
            'title' => ['required', 'unique:products,title', 'string'],
            'rate' => ['required', 'numeric', 'min:1'],
            'image_url' => 'string'
        ]);

        $product = Product::create($request->only(['title', 'rate', 'image_url']));

        if ($product) {
            return response()->json($product);
        }

        return response()->json(['message' => 'Failed to Store New Product'], 500);
    }

    public function update(Request $request)
    {
        $this->validate($request, ['id' => ['required', 'numeric', 'exists:products,id'], 'title' => ['required', 'string', 'unique:products,title,' . $request->id], 'rate' => ['required', 'numeric', 'min:1'], 'image_url' => ['nullable', 'string']]);
        $product = Product::findOrFail($request->id);
        $product->title = $request->title;
        $product->rate = $request->rate;
        $product->image_url = $request->input('image_url', $product->image_url);
        $result = $product->save();

        if ($result) {
            return response()->json($product->refresh());
        }
        return response()->json(['message' => 'Failed to Update Product'], 500);
    }
}
