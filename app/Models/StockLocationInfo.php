<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockLocationInfo extends Model
{

    protected $table = 'stock_location_info';

    protected $fillable = [
        'product_id',
        'location_id',
        'quantity',
    ];

    protected $hidden = [
        'created_at', 'updated_at'
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function location()
    {
        return $this->belongsTo(Location::class);
    }
}