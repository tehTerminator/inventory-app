<?php

namespace App\Http\Controllers;

use App\Models\Voucher;
use App\Services\VoucherService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VoucherController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(){  }

    public function select(Request $request) {
        $this->validate($request, [
            'fromDate' => 'required|date',
            'ledger' => 'required|integer|min:1'
        ]);

        $ledger = $request->query('ledger');
        $from_date = $request->query('fromDate');
        $to_date = $request->query('toDate', $from_date);

        $response = VoucherService::select($ledger, $from_date, $to_date);
        return response()->json($response);
    }

    public function getById(int $id) {
        // return response($id);
        $voucher = Voucher::findOrFail($id);
        return response()->json($voucher);
    }

    public function create(Request $request) {
        $this->validate($request, [
            'cr' => 'required|integer',
            'dr' => 'required|integer',
            'narration' => 'string',
            'amount' => 'required|numeric',
        ]);

        $voucher = VoucherService::create($request->all());
        return response()->json($voucher);
    }

    public function update(Request $request) {
        $this->validate($request, [
            'id' => 'required|integer',
            'cr' => 'required|integer',
            'dr' => 'required|integer',
            'narration' => 'string',
            'amount' => 'required|numeric',
        ]);
        $voucher = VoucherService::update($request->all());
        return response()->json($voucher);
    }
}
