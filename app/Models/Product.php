<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model {
    use SoftDeletes;

    protected $fillable = [ 'title', 'rate', 'category_id', 'image_url' ];

    protected $casts = [
        'rate' => 'integer'
    ];

    protected $dates = [ 'deleted_at' ];

    public function category() {
        $this->belongsTo( Category::class );
    }
}