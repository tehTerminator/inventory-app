<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{

    protected $table = 'products';

    protected $fillable = [
        'title',
        'group_id',
    ];

    public function group()
    {
        return $this->belongsTo(ProductGroup::class, 'group_id', 'id');
    }
}
