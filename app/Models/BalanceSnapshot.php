<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BalanceSnapshot extends Model {

    protected $table = 'balance_snapshots';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'ledger_id', 'opening', 'closing'
    ];

    public function ledger() {
        return $this->belongsTo(Ledger::class);
    }
}
