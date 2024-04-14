<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{

    protected $table = 'products';

    protected $fillable = [
        'title',
        'rate',
        'expiry_date',
    ];

    protected $hidden = [
        'created_at', 'updated_at'
    ];
}
