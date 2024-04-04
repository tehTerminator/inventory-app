<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{

    protected $table = 'contacts';

    protected $fillable = [
        'title',
        'address',
        'mobile',
        'kind',
        'ledger_id'
    ];

    protected $hidden = [
        'created_at', 'updated_at'
    ];

    public function ledger()
    {
        return $this->hasOne(Ledger::class);
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    public function paymentInfos()
    {
        return $this->hasMany(InvoicePaymentInfo::class);
    }

    public function scopeCustomer($query)
    {
        return $query->where('kind', 'CUSTOMER');
    }

    public function scopeSupplier($query)
    {
        return $query->where('kind', 'SUPPLIER');
    }
}
