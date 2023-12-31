<?php

use Illuminate\Database\Eloquent\Model;

class DetailedTransaction extends Model
{
    protected $fillable = [
        'invoice_id',
        'item_id',
        'item_type',
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
}