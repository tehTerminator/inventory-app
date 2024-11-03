<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model {
    protected $fillable = [ 'customer_id', 'amount', 'discount' ];
    protected $casts = [
        'amount' => 'integer',
        'discount' => 'integer'
    ];

    public function customer() {
        return $this->belongsTo( Customer::class );
    }

    public function orders() {
        return $this->hasMany(Order::class)->where('status', 'COMPLETED')->orWhere('status', 'PAID');
    }

    public function getNetAmountAttribute() {
        return $this->amount - $this->discount;
    }
}