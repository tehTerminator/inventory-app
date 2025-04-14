<?php

namespace App\Http\Controllers;

use App\Models\Voucher;
use App\Services\VoucherService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\Ledger;

class VoucherController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
    }

    public function select(Request $request)
    {
        $this->validate($request, [
            'fromDate' => 'required|date',
            'ledger' => 'required|decimal:0,2|min:1'
        ]);

        $ledger = $request->query('ledger');
        $from_date = $request->query('fromDate');
        $to_date = $request->query('toDate', $from_date);

        $response = VoucherService::select($ledger, $from_date, $to_date);
        return response()->json($response);
    }

    public function getRecent(Request $request) {
        $vouchers = Voucher::orderBy('id', 'desc')->take(5)->get();
        return response()->json($vouchers);
    }

    public function getById(int $id)
    {
        // return response($id);
        $voucher = Voucher::findOrFail($id);
        return response()->json($voucher);
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'cr' => 'required|integer',
            'dr' => 'required|integer',
            'narration' => 'string',
            'amount' => 'required|decimal:0,2',
        ]);

        $voucher = VoucherService::create($request->all());
        return response()->json($voucher);
    }

    public function update(Request $request)
    {
        $this->validate($request, [
            'id' => 'required|integer',
            'cr' => 'required|integer',
            'dr' => 'required|integer',
            'narration' => 'string',
            'amount' => 'required|decimal:0,2',
        ]);
        $voucher = VoucherService::update($request->all());
        return response()->json($voucher);
    }

    public function dayBook(Request $request)
    {
        $date = $request->query('date');
        return VoucherService::dayBook($date);
    }

    public function todaysIncome()
    {
        $today = date('Y-m-d');

        $incomeLedgers = Ledger::select('id')->whereIn('kind', ['INCOME', 'SALES AC'])->get()->toArray();
        $totalAmount = Voucher::select('amount')
            ->whereIn('cr', $incomeLedgers)  // Use whereIn for multiple IDs
            ->where('created_at', 'like', $today . '%')
            ->sum('amount');

        $response = ['data' => 'Rs. ' . floor($totalAmount)];
        return response()->json($response);
    }

    public function todaysExpense()
    {
        $today = date('Y-m-d');

        $incomeLedgers = Ledger::select('id')->whereIn('kind', ['EXPENSE', 'PURCHASE AC', 'DUTIES AND TAXES'])->get()->toArray();
        $totalAmount = Voucher::select('amount')
            ->whereIn('dr', $incomeLedgers)  // Use whereIn for multiple IDs
            ->where('created_at', 'like', $today . '%')
            ->sum('amount');

        $response = ['data' => 'Rs. ' . floor($totalAmount)];
        return response()->json($response);
    }
}
