<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ledger extends Model {

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'title', 'kind', 'can_receive_payment'
    ];

    protected $hidden = [
        'created_at', 'updated_at'
    ];

    public function balance_snapshot() {
        return $this->hasMany(BalanceSnapshot::class);
    }
}