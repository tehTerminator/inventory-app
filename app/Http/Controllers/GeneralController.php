<?php

namespace App\Http\Controllers;

use App\Models\Ledger;
use Exception;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Request;


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

    public function getGeneralItems(Request $request)
    {

        $this->validate($request, ['locationId' => 'required|exists:locations,id']);
        $locationId = $request->input('locationId');

        $generalItems = Cache::remember(
            'generalItem' . $locationId,
            3600,
            function () use ($locationId) {

                $ledgers = Ledger::select('id', 'title')->whereIn('kind', ['BANK', 'CASH', 'WALLET'])->get();

                $bundles = DB::table('bundles AS b')
                    ->select('b.id', 'b.title', 'b.rate')
                    ->leftJoin('bundles__templates AS bt', 'b.id', '=', 'bt.bundle_id')
                    ->leftJoin('stock_location_infos AS st', 'bt.kind', '=', 'st.product_id')
                    ->where(function ($query) use ($locationId) {
                        $query->where('bt.kind', '=', 'LEDGER')
                            ->orWhere(function ($query) use ($locationId) {
                                $query->where('bt.kind', '=', 'PRODUCT')
                                    ->whereNull('st.product_id')
                                    ->orWhere('st.location_id', '=', $locationId);
                            });
                    })
                    ->distinct()
                    ->get();

                $products = DB::table('stock_location_infos')
                    ->join('products', 'stock_location_infos.product_id', '=', 'products.id')
                    ->where('stock_location_infos.location_id', $locationId)
                    ->get();


                $items = [];

                foreach ($products as $item) {
                    $item->type = 'PRODUCT';
                    array_push($items, $item);
                }

                foreach ($ledgers as $item) {
                    $item->type = 'LEDGER';
                    $item['rate'] = 0;
                    array_push($items, $item);
                }

                foreach ($bundles as $item) {
                    $item->type = 'BUNDLE';
                    array_push($items, $item);
                }

                return $items;
            }
        );

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
