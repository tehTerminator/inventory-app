<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductGroup;
use Illuminate\Http\Request;
use App\Services\ProductService;

class ProductController extends Controller
{
    public function createGroup(Request $request)
    {
        $this->validate($request, [
            'title' => 'required|unique:product_groups,title',
        ]);
        $group = ProductGroup::create($request->only('title'));
        return response()->json($group);
    }

    public function indexGroups() {
        return response()->json(ProductGroup::all());
    }

    public function getGroupById($id) {
        $group = ProductGroup::findOrFail($id);
        return response()->json($group);
    }

    public function updateGroup(Request $request) {
        $this->validate($request, [
            'id' => 'required|exists:product_groups,id',
            'title' => 'required|unique:product_groups,title'
        ]);
        $group = ProductGroup::findOrFail($request->id);
        $group->title = $request->title;
        $group->save();
        return response()->json($group);
    }

    public function createProduct(Request $request)
    {
        $this->validate($request, [
            'title' => 'required|unique:products,title',
            'group_id' => 'required|numeric|exists:product_groups,id',
            'quantity' => 'numeric',
            'location_id' => 'numeric'
        ]);

        ProductService::createProduct(
            $request->input('title'),
            $request->input('group_id'),
            $request->input('location_id', 0),
            $request->input('quantity', 0)
        );
    }

    public function indexProducts(Request $request)
    {
        $pageLength = $request->input('pageLength', 10);
        $currentPage = $request->input('currentPage', 1);
        $skip = ($currentPage - 1) * $pageLength;

        return Product::skip($skip)->take($pageLength)->get();
    }


    public function getProductsByTitle(Request $request)
    {
        $this->validate($request, [
            'location_id' => 'required|exists:App/Models/Location,id',
            'title' => 'required'
        ]);

        $title = $request->input('title') . '%';
        $locationId = $request->input('locationId');

        $products = ProductService::getProductFromLocation($locationId, $title);
        return response()->json($products);
    }

    public function getProductById($id) {
        $product = Product::findOrFail($id);
        return response()->json($product);
    }
}
