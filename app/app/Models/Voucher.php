<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Voucher extends Model {

    use SoftDeletes;
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

    public function creditor() {
        return $this->belongsTo(Ledger::class, 'cr', 'id');
    }

    public function debtor() {
        return $this->belongsTo(Ledger::class, 'dr', 'id');
    }

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function invoicePaymentInfo() {
        return $this->belongsTo(InvoicePaymentInfo::class);
    }
}
