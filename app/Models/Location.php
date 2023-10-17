<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    protected $table = 'locations';

    protected $fillable = [
        'title',
    ];

    public function getNameAttribute()
    {
        return ucfirst($this->attributes['title']);
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
