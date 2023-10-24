<?php

namespace App\Services;

use App\Models\Product;
use App\Models\StockLocationInfo;
use App\Models\StockTransferInfo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ProductService
{
    public static function createProduct(string $title, $group_id, $location_id = 0, $quantity = 0)
    {
        DB::beginTransaction();

        try{
            $product = Product::create([
                'title' => $title,
                'group_id' => $group_id
            ]);
    
            if ($location_id == 0) {
                return $product;
            }
    
            $user_id = Auth::user()->id;
    
            StockLocationInfo::create([
                'product_id' => $product->id,
                'location_id' => $location_id,
                'quantity' => $quantity
            ]);
    
            StockTransferInfo::create([
                'product_id' => $product->id,
                'to_location_id' => $location_id,
                'narration' => 'New Product',
                'quantity' => $quantity,
                'balance_quantity' => $quantity,
                'user_id' => $user_id
            ]);

            DB::commit();
    
            return $product;
        } catch(\Exception $e) {
            DB::rollBack();
            return response($e, 500);
        }

    }

    public static function addProduct($product_id, $location_id, $quantity)
    {
        $location_info = StockLocationInfo::where('location_id', $location_id)->where('product_id', $product_id);

        if (empty($location_info)) {
            StockLocationInfo::create([
                'product_id' => $product_id,
                'location_id' => $location_id,
                'quantity' => $quantity
            ]);
            return;
        }

        $location_info->quantity += $quantity;
        $location_info->save();
    }

    public static function consumeProduct($product_id, $location_id, $quantity)
    {
        $location_info = StockLocationInfo::where('location_id', $location_id)->where('product_id', $product_id);
        $location_info->quantity -= $quantity;
        $location_info->save();
    }

    public static function getProductFromLocation(int $location_id) {
        
    }

    public static function searchProductFromLocation($location_id, $title)
    {

        $product = Product::where('title', $title)
            ->whereIn('id', function ($query) use ($location_id) {
                $query->select('product_id')
                    ->from('stock_location_info')
                    ->where('location_id', $location_id);
            })
            ->get();

        return $product;
    }

    public static function getProducts(Request $request) {
        $pageLength = $request->input('pageLength', 10);
        $currentPage = $request->input('currentPage', 1);
        $skip = ($currentPage - 1) * $pageLength;

        return Product::skip($skip)->take($pageLength)->get();
    }
}
