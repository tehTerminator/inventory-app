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
            'title' => 'required|exists:App/Models/ProductGroup,title',
        ]);
        $group = ProductGroup::create($request->only('title'));
        return response()->json($group);
    }

    public function createProduct(Request $request)
    {
        $this->validate($request, [
            'title' => 'required|unique:App/Models/Product,title',
            'group_id' => 'required|exists:App/Models/ProductGroup,id',
            'quantity' => 'integer',
            'location_id' => 'integer,exists:App/Models/Location,id'
        ]);

        ProductService::createProduct(
            $request->input('title'),
            $request->input('group_id'),
            $request->input('location_id', 0),
            $request->input('quantity', 0)
        );
    }

    public function index(Request $request)
    {
        $pageLength = $request->input('pageLength', 10);
        $currentPage = $request->input('currentPage', 1);
        $skip = ($currentPage - 1) * $pageLength;

        return Product::skip($skip)->take($pageLength)->get();
    }

    public function getProducts(Request $request)
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
}
