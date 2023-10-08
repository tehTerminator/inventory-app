<?php

namespace Database\Seeders;

use App\Models\Ledger;
use Illuminate\Database\Seeder;

class LedgerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data = [
            [
                'title' => 'Cash', 
                'group_id' => 2, 
                'can_receive_payment' => true
            ],
            [
                'title' => 'Walk in Customer',
                'group_id' => 3,
                'can_receive_payment' => false
            ],
        ];

        foreach($data as $row) {
            Ledger::create($row);
        }
    }
}
