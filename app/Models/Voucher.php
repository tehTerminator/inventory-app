<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Voucher extends Model
{
    protected $table = 'vouchers';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'cr',
        'dr',
        'narration',
        'amount',
        'user_id',
        'immutable',
    ];

    protected $hidden = [
        'immutable'
    ];

    protected $casts  = [
        'cr' => 'integer',
        'dr' => 'integer',
        'amount' => 'double',
    ];

    // Add this accessor method to format the amount to two decimal places
    public function getAmountAttribute($value)
    {
        return number_format($value, 2, '.', '');
    }

    public function creditor()
    {
        return $this->belongsTo(Ledger::class, 'cr', 'id');
    }

    public function debtor()
    {
        return $this->belongsTo(Ledger::class, 'dr', 'id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function invoicePaymentInfo()
    {
        return $this->belongsTo(InvoicePaymentInfo::class);
    }
}
