<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvoicePaymentInfo extends Model
{

    protected $table = 'invoice_payment_info';

    protected $fillable = [
        'invoice_id',
        'contact_id',
        'voucher_id',
        'amount',
    ];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function contact()
    {
        return $this->belongsTo(Contact::class);
    }

    public function voucher()
    {
        return $this->belongsTo(Voucher::class);
    }
}
