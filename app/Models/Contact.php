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
    ];

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    public function paymentInfos()
    {
        return $this->hasMany(InvoicePaymentInfo::class);
    }
}
