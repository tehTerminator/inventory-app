<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductGroup extends Model
{

    protected $table = 'product_groups';

    protected $fillable = [
        'title',
    ];

    protected $hidden = [
        'create_at',
        'updated_at'
    ];

    public function products()
    {
        return $this->hasMany(Product::class, 'group_id', 'id');
    }
}
