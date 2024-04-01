<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bundle extends Model
{
    protected $table = 'bundles';

    protected $fillable = [
        'title',
        'rate'
    ];

    protected $hidden = [
        'created_at', 'updated_at'
    ];

    public function templates()
    {
        return $this->hasMany(BundleTemplate::class, 'bundle_id');
    }
}
