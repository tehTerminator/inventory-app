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
        /* ALLOWED KIND
            [
                'CAPITAL',
                'BANK',
                'WALLET',
                'DEPOSIT',
                'CASH',
                'PAYABLE',
                'RECEIVABLE',
                'EXPENSE',
                'INCOME',
                'PURCHASE AC',
                'SALES AC',
                'DUTIES AND TAXES'
            ]
         */
        $ledgers = [
            [
                'title' => 'Cash',
                'kind' => 'CASH',
                'can_receive_payment' => true,
            ],
            [
                'title' => 'Sales Account',
                'kind' => 'SALES AC',
                'can_receive_payment' => false,
            ],
            [
                'title' => 'Purchase Account',
                'kind' => 'PURCHASE AC',
                'can_receive_payment' => false,
            ],
        ];

        foreach ($ledgers as $ledgerData) {
            Ledger::create($ledgerData);
        }
        

        $supplier = Ledger::create([
            'title' => 'Suppliers', 
            'kind' => 'PAYABLE', 
            'can_receive_payment' => false
        ]);

        $walkInCustomer = Ledger::create([
            'title' => 'Walk-in Customer',
            'kind' => 'RECEIVABLE',
            'can_receive_payment' => false
        ]); 

        Contact::create([
            'title' => 'Walk-in Customer',
            'address' => 'Ashoknagar',
            'mobile' => '99999999',
            'kind' => 'CUSTOMER',
            'ledger_id' => $walkInCustomer->id
        ]);
        Contact::create([
            'title' => 'Supplier',
            'address' => 'Ashoknagar',
            'mobile' => '99999999',
            'kind' => 'SUPPLIER',
            'ledger_id' => $supplier->id
        ]);
    }
}
