<?php

namespace App\Http\Controllers;

use App\Models\Voucher;
use App\Services\VoucherService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VoucherController extends Controller
{
    private $ledger = 0;
    private $service = NULL;
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(){  }

    public function select(Request $request) {
        $this->validate($request, [
            'id' => 'integer|min:1',
        ]);

        if ($request->has('id')) {
            return response()->json(Voucher::findOrFail($request->query('id')));
        }

        $this->validate($request, [
            'fromDate' => 'required|date',
            'ledger' => 'required|integer|min:1'
        ]);

        $this->ledger = $request->query('ledger');
        $from_date = $request->query('fromDate');
        $to_date = $request->query('toDate', $from_date);

        $response = VoucherService::select($this->ledger, $from_date, $to_date);
        return response()->json($response);
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
        VoucherService::update($request->all());
    }
}
