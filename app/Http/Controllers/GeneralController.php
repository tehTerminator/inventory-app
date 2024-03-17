<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Ledger;
use App\Models\Bundle;
use Exception;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;


class GeneralController extends Controller
{

    private $_validTables = [
        'balance_snapshot',
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
        'vouchers'
    ];

    private $TYPE = [
        'PRODUCT' => 0,
        'LEDGER' => 1,
        'BUNDLE' => 2,
    ];

    public function getById(string $table, int $id)
    {
        $query = $this->validateAndGetQuery($table . 's');
        $query->where('id', $id);
        return response()->json($query->get()->take(1));
    }

    public function select(string $table)
    {

        try {
            $query = $this->validateAndGetQuery($table);
        } catch (Exception $e) {
            return response(['message' => $e->getMessage()], 400);
        }

        // Apply options if provided
        $queryParams = request()->query();

        if (is_array($queryParams)) {
            $this->applyWhereClaus($query, $queryParams);
        }

        return response()->json($query->get());
    }

    public function destroy(string $table, int $id)
    {
        $query = $this->validateAndGetQuery($table . 's');
        $query->where('id', $id);
        if ($query->delete() == 0) {
            return response('No record Found', 400);
        }
        return response()->json(["message" => "Successfully Deleted Item"]);
    }

    public function getGeneralItems()
    {
        $generalItems = Cache::remember('generalItem', 3600, function () {
            $products = Product::select('id', 'title', 'rate')->get();
            $ledgers = Ledger::select('id', 'title')->whereIn('kind', ['BANK', 'CASH', 'WALLET'])->get();
            $bundles = Bundle::select('id', 'title', 'rate')->get();

            $items = [];

            foreach ($products as $item) {
                $item['type'] = $this->TYPE['PRODUCT'];
                array_push($items, $item);
            }

            foreach ($ledgers as $item) {
                $item['type'] = $this->TYPE['LEDGER'];
                $item['rate'] = 0;
                array_push($items, $item);
            }

            foreach ($bundles as $item) {
                $item['type'] = $this->TYPE['BUNDLE'];
                array_push($items, $item);
            }

            return $items;
        });

        return response()->json($generalItems);
    }

    private function validateAndGetQuery(string $table)
    {
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $table)) {
            throw new Exception('Invalid Table Name');
        }

        if (!in_array($table, $this->_validTables)) {
            throw new Exception('Invalid Table Name');
        }

        return DB::table($table);
    }

    private function applyWhereClaus(Builder $query, array $params)
    {
        foreach ($params as $coldata => $value) {
            if (strpos($coldata, ':') > 1) {
                $coldata = explode(':', $coldata);
                $column = $this->camelCaseToSnakeCase($coldata[0]);
                $operator = $this->validateOperator($coldata[1]);
                $query->where($column, $operator, $value);
            } else {
                $query->where($this->camelCaseToSnakeCase($coldata), $value);
            }
        }

        return $query;
    }

    private function validateOperator($operator)
    {
        if ($operator == 'gt') {
            return '>';
        }

        if ($operator == 'lt') {
            return '<';
        }

        if ($operator == 'gteq') {
            return '>=';
        }

        if ($operator == 'lteq') {
            return '<=';
        }

        return $operator;
    }

    private function camelCaseToSnakeCase($str)
    {
        $snakeCase = strtolower(preg_replace('/([a-z])([A-Z])/', '$1_$2', $str));
        return $snakeCase;
    }
}
