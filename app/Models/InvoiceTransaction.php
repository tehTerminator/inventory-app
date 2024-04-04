<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvoiceTransaction extends Model
{
    protected $table = 'invoices_transactions';

    protected $fillable = [
        'invoice_id',
        'item_id',
        'item_type',
        'user_id',
        'quantity',
        'rate',
        'discount',
        'is_child'
    ];

    protected $hidden = [
        'is_child'
    ];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}