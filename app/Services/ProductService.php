<?php

namespace App\Services;

use App\Models\Product;
use App\Models\StockLocationInfo;
use App\Models\StockTransferInfo;

class ProductService
{
    public static function createProduct(string $title, $group_id, $location_id = 0, $quantity = 0)
    {
        $product = Product::create([
            'title' => $title,
            'group_id' => $group_id
        ]);

        if ($location_id == 0) {
            return $product;
        }

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
            'balance_quantity' => $quantity
        ]);

        return $product;
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

    public static function getProductFromLocation($location_id, $title)
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
}
