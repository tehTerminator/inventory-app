<?php

namespace Database\Seeders;

use App\Models\Group;
use Illuminate\Database\Seeder;

class GroupSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data = [
            'Bank,ASSETS',
            'Cash,ASSETS',
            'Receivable,ASSETS',
            'Payable,LIABILITIES',
            'Income,INCOME',
            'Expenses,EXPENSES'
        ];
        foreach ($data as $value) {
            $extractedData = explode(',', $value);
            Group::create([
                'title' => $extractedData[0], 
                'kind' => $extractedData[1]
            ]);
        }
    }
}
