<?php

namespace App\Models; 

class RulesService {
    private $data = [
        'balance_snapshot' => [
            'class' => BalanceSnapshot::class,
            'rules' => [
                'ledger_id' => 'required|integer|exists:Ledger,id',
                'opening' => 'required|integer',
                'closing' => 'required|integer'
            ]
        ],
        'contacts' => [
            'class' => Contact::class,
            'rules' => [
                'kind' => 'required|'
            ]
        ],
        'groups' => [
            'class' => Group::class,
            'rules' => [

            ]
        ],
        'invoices' => [
            'class' => Invoice::class,
            'rules' => []
        ],
        'invoices_transactions' => [
            'class' => InvoiceTransaction::class,
            'rules' => []
        ],
        'invoice_payment_infos' => [
            'class' => InvoicePaymentInfo::class,
            'rules' => []
        ],
        'ledgers' => [
            'class' => Ledger::class,
            'rules' => []
        ], 
        'locations' => [
            'class' => Location::class,
            'rules' => []
        ],
        'location_users' => [
            'class' => LocationUser::class,
            'rules' => []
        ],
        'products' => [
            'class' => Product::class,
            'rules' => []
        ],
        'stock_location_infos' => [
            'class' => StockLocationInfo::class,
            'rules' => []
        ],
        'stock_transfer_infos' => [
            'class' => StockTransferInfo::class,
            'rules' => []
        ],
        'bundles' => [
            'class' => Bundle::class,
            'rules' => []
        ],
        'bundles__templates' => [
            'class' => BundleTemplate::class,
            'rules' => []
        ],
        'detailed_transactions' => [
            'class' => DetailedTransaction::class,
            'rules' => []
        ],    
        'vouchers' => [
            'class' => Voucher::class,
            'rules' => [
                'cr' => 'required|integer|exists:App\Ledger,id',
                'dr' => 'required|integer|exists:App\Ledger,id',
                'narration' => 'string',
                'amount' => 'required|numeric|min:0.1',
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