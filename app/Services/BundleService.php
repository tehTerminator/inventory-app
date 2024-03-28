<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use App\Models\Bundle;
use Illuminate\Support\Facades\DB;
use App\Models\BundleTemplate;

class BundleService {

    // To Be Implemented Later
    // private static $bundleValidationRules = [
    //     'id' => [
    //         'exists:App\Models\Bundle,id', 'required', 'min:1'
    //     ],
    //     'title' => [
    //         'unique:App\Models\Bundle,title', 'required', 'min:3'
    //     ],
    //     'rate' => [
    //         'required', 'numeric', 'min:1'
    //     ]
    // ];

    public static function selectBundle() {
        $bundles = Cache::remember('Bundles', 6000, function() {
            return Bundle::with(['templates'])->get();
        });
        return $bundles;
    }

    public static function createBundle(string $title, float $rate) {

        return Bundle::create([
            'title' => $title,
            'rate' => $rate
        ]);
        Cache::forget('Bundles');
    }

    public static function updateBundle(int $id, string $title, float $rate) {
        $bundle = Bundle::findOrFail($id);
        $bundle->title = $title;
        $bundle->rate = $rate;
        $bundle->save();

        Cache::forget('Bundles');
        return $bundle;
    }

    public static function deleteBundle(int $bundle_id) {
        
        $Bundle = Bundle::findOrFail($bundle_id);
        
        DB::beginTransaction();

        try {
            BundleTemplate::where('Bundle_id', $Bundle->id)->delete();
            $Bundle->delete();
            Cache::forget('Bundles');
            DB::commit();
        } catch (\Exception $ex) {
            DB::rollBack();
        }
        
        return response()->json(['message'=>'Bundle Deleted Successfully']);
    }

    public static function createTemplate(
        int $bundle_id,
        int $item_id,
        string $kind,
        float $rate,
        float $quantity
    ) {
        return BundleTemplate::create([
            'bundle_id' => $bundle_id,
            'item_id' => $item_id,
            'kind' => $kind,
            'rate' => $rate,
            'quantity'=>$quantity
        ]);
    }

    public static function deleteTemplate(int $id) {
        BundleTemplate::findOrFail($id)->delete();
        return response()->json(['message'=>'Template Deleted Successfully']);
    }

    public function __construct() {}

}
