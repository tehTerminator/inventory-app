<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    protected $table = 'locations';

    protected $fillable = [
        'name',
    ];

    public function getNameAttribute()
    {
        return ucfirst($this->attributes['name']);
    }

    public function products()
    {
        return $this->hasManyThrough(
            Product::class,
            StockLocationInfo::class,
            'location_id',
            'product_id'
        );
    }
}
