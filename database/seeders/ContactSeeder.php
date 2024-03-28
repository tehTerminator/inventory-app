<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Contact;

class ContactSeeder extends Seeder
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
                'title' => 'Walk-in Customer',
                'address' => 'Ashoknagar',
                'mobile' => '123456789',
                'kind' => 'CUSTOMER',
            ],
            [
                'title' => 'Supplier',
                'address' => 'Ashoknagar',
                'mobile' => '123456789',
                'kind' => 'SUPPLIER'
            ]
        ];

        foreach($data as $item) {
            Contact::create($item);
        }
    }
}
