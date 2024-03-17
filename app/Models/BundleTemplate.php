<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BundleTemplate extends Model
{
    protected $table = 'bundles__templates';

    protected $fillable = [
        'bundle_id', 'item_id', 'kind', 'rate', 'quantity'
    ];
    
    public function bundle()
    {
        return $this->belongsTo(Bundle::class, 'bundle_id');
    }
}