<?php

namespace App\Http\Controllers;

use App\Models\BalanceSnapShot;
use App\Models\Ledger;
use Illuminate\Http\Request;
use App\Services\LedgerService;
use Carbon\Carbon;

class LedgerController extends Controller
{
    public function __construct()
    {
        //
    }

    public function select() {
        return response()->json(Ledger::all());
    }

    public function store(Request $request) {
        $this->validate($request, LedgerService::getValidationRules(true));
        
        return response()->json(
            LedgerService::createLedger(
                $request->title,
                $request->kind,
                $request->input('balance', 0),
                $request->input('canReceivePayment', false)
            ));
    }

    public function update(Request $request) {
        $this->validate(
            $request, LedgerService::getValidationRules(false, true)
        );
        return response()->json(
            LedgerService::updateLedger(
                $request->id,
                $request->title,
                $request->input('kind'),
                $request->input('canReceivePayment')
            )
        );
    }

    public function autoUpdateBalance() {
        return response()->json(LedgerService::autoUpdateBalance());
    }

    public function updateBalance(Request $request) {
        $this->validate($request, [
            'id' => 'required|integer|exists:App\Models\Ledger,id',
            'opening' => 'required|numeric',
            'closing' => 'required|numeric',
        ]);
        $balance = LedgerService::updateBalance($request->id, $request->opening, $request->closing);
        return response()->json($balance);
    }

    public function selectBalance(Request $request) {
        $date = $request->query('date', Carbon::now());

        $assets = Ledger::whereIn('kind', ['BANK', 'CASH', 'WALLET'])->pluck('id')->toArray();
        $data = BalanceSnapshot::whereDate('created_at', $date)->whereIn('ledger_id', $assets)
        ->with(['ledger'])->get();
        return response($data);
    }
}
