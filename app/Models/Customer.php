<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model {
    protected $fillable = ['title', 'mobile'];

    public function invoices() {
        return $this->hasMany(Invoice::class);
    }
}