<?php

namespace App\Http\Controllers;

use App\Models\Ledger;
use Exception;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\User;
use App\Models\Bundle;
use App\Models\Location;
use App\Models\Contact;

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

    public function indexData()
    {
        $data = [];
        $data['products'] = Product::all();
        $data['ledgers'] = Ledger::all();
        $data['users'] = User::select(['id', 'name'])->get();
        $data['bundles'] =  Bundle::with('templates')->get();
        $data['locations'] = Location::all();
        $data['contacts'] = Contact::all();
        return response()->json($data);
    }

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
            return response()->json(['message' => $e->getMessage()], 400);
        }

        // Apply options if provided
        $queryParams = request()->query();
        $data = NULL;

        if (is_array($queryParams)) {
            $this->applyWhereClaus($query, $queryParams);
            $data = $query->get();
        } else {
            return $query->get();
        }
        return response()->json($data);
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
        Cache::clear();
        try{
        $this->validate($request, ['locationId' => 'required|exists:locations,id']);
        $locationId = $request->input('locationId');

        $generalItems = Cache::remember(
            'generalItem',
            5,
            function () {

                $ledgers = Ledger::select('id', 'title')->where('can_pay', true)->get();
                $bundles = Bundle::all();
                $products = Product::all();

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
    } catch (\Exception $ex) {
        return response()->json(['message' => $ex->getMessage()]);
    }
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
