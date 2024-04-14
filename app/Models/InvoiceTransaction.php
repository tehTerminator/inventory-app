<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvoiceTransaction extends Model
{

    protected $table = 'invoices_transactions';

    protected $fillable = [
        'invoice_id',
        'product_id',
        'user_id',
        'quantity',
        'rate',
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
        return $this->hasOne(Product::class);
    }
}
