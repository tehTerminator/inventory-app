<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    protected $table = 'groups';

    protected $fillable = [
        'title',
        'kind',
    ];

    public function ledgers()
    {
        return $this->hasMany(Ledger::class);
    }
}