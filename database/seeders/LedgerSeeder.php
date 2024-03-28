<?php

namespace Database\Seeders;

use App\Models\Ledger;
use Illuminate\Database\Seeder;
use App\Models\Contact;

class LedgerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Ledger::create([
            'title' => 'Cash', 
            'kind' => 'CASH', 
            'can_receive_payment' => true
        ]);

        $walkInCustomer = Ledger::create([
            'title' => 'Walk-in Customer',
            'kind' => 'RECEIVABLE',
            'can_receive_payment' => false
        ]); 

        $contact = Contact::where('title', 'Walk-in Customer')->first();
        $contact->ledger_id = $walkInCustomer->id;
        $contact->save();
        
    }
}
