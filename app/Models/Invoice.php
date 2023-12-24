<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory;

    protected $table = 'invoices';

    protected $fillable = [
        'kind',
        'contact_id',
        'location_id',
        'paid',
        'amount',
        'user_id',
    ];

    protected $casts = [
        'location_id' => 'integer',
        'contact_id' => 'integer',
        'user_id' => 'integer',
        'amount' => 'double',
    ];

    public function contact()
    {
        return $this->belongsTo(Contact::class);
    }

    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function transactions()
    {
        return $this->hasMany(InvoiceTransaction::class);
    }
}
