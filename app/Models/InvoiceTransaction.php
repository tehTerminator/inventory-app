<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvoiceTransaction extends Model
{
    protected $fillable = [
        'invoice_id',
        'description',
        'user_id',
        'quantity',
        'rate',
        'discount',
    ];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}