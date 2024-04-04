<?php

namespace App\Services;


class RulesService {
    private $data = [
        'balance_snapshot' => [
            'class'
        ],
        'contacts',
        'groups',
        'invoices',
        'invoices_transactions',
        'invoice_payment_infos',
        'ledgers', 
        'locations',
        'location_users',
        'products',
        'product_groups',
        'stock_location_infos',
        'stock_transfer_infos',
        'bundles',
        'bundles__templates',
        'detailed_transactions',    
        'vouchers' => [
            'rules' => [
                'cr' => 'required|integer',
                'dr' => 'required|integer',
                'narration' => 'string',
                'amount' => 'required|numeric',
            ]
        ],
    ];

    public function isValidTable($table) {
        return in_array($table, $this->getValidTables());
    }

    public function getValidTables() {
        return array_keys($this->data);
    }

    public function getClass($table) {
        return $this->data['class'];
    }

    
}