<?php

namespace App\Http\Controllers;

use App\Models\InvoiceTransaction;
use App\Models\Voucher;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use App\Models\Ledger;
use Illuminate\Support\Facades\DB;


class OverviewReportController extends Controller
{

    public function mySales()
    {
        $user_id = Auth::user()->id;

        $currDate = Carbon::now()->format('Y-m-d');
        $sales_ledger = Ledger::where('title', 'Sales Account')->first();
        $amount = Voucher::where('created_at', 'LIKE', $currDate . '%')
            ->where('cr', $sales_ledger->id)->where('user_id', $user_id)->sum('amount');

        $response = ['data' => 'Rs. ' . floor($amount)];
        return response()->json($response);
    }


    public function myCommission()
    {
        $user_id = Auth::user()->id;

        $currDate = Carbon::now()->format('Y-m-d');
        $commission_ledger = Ledger::where('title', 'Commission')->first();
        $amount = Voucher::where('created_at', 'LIKE', $currDate . '%')
            ->where('cr', $commission_ledger->id)->where('user_id', $user_id)->sum('amount');

        $response = ['data' => 'Rs. ' . floor($amount)];
        return response()->json($response);
    }

    public function productsUsed()
    {
        $currDate = Carbon::now()->format('Y-m-d');
        $transactions = InvoiceTransaction::select('item_id', 'item_type', 'rate', DB::raw('sum(quantity) as total_quantity'))
            ->where('created_at', 'LIKE', $currDate . '%')
            ->where('is_child', false)
            ->where(function ($query) {
                $query->where('item_type', 'PRODUCT')
                    ->orWhere('item_type', 'BUNDLE');
            })
            ->groupBy('item_id', 'item_type', 'rate')->get();

        return response()->json($transactions);
    }

    public function __construct()
    {
    }
}
