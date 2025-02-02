<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class Category extends Model
{
    protected $table = 'Categories';

    protected $fillable = ['title'];

    public function Products()
    {
        $this->hasMany(Product::class);
    }
}
