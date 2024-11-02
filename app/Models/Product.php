<?php

use Illuminate\Database\Eloquent\Model;

class Product extends Model {
    use SoftDeletes; 
    protected $fillable = ['title', 'rate', 'image_url'];

    protected $casts = [
        'rate' => 'integer'
    ];

    protected $dates = ['deleted_at'
}