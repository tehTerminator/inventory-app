<?php

namespace App\Services;

use App\Models\Ledger;
use App\Models\Voucher;
use Illuminate\Support\Carbon;
use App\Models\BalanceSnapShot;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class LedgerService
{

    protected static $validationRules = [
        'id' => ['integer', 'exists:App\Models\Ledger,id'],
        'title' => ['required', 'string'],
        'kind' => ['required', 'in:CAPITAL,BANK,WALLET,DEPOSIT,CASH,PAYABLE,RECEIVABLE,EXPENSE,INCOME,PURCHASE AC,SALES AC,DUTIES AND TAXES'],
    ];

    public static function createLedger(
        string $title,
        string $kind,
        $can_receive_payment = false
    ) {
        $ledger = Ledger::create([
            'title' => $title,
            'kind' => $kind,
            'can_receive_payment' => $can_receive_payment
        ]);
        return $ledger;
    }

    public static function updateLedger(
        int $id,
        string $title,
        string $kind = NULL,
        bool $can_receive_payment = NULL
    ) {
        $ledger = Ledger::findOrFail($id);
        $ledger->title = $title;

        if (!is_null($kind)) {
            $ledger->kind = $kind;
        }

        if (!is_null($can_receive_payment)) {
            $ledger->can_receive_payment = $can_receive_payment;
        }

        $ledger->save();
        $ledger->refresh();
        return $ledger;
    }

    public static function autoUpdateBalance()
    {

        // return $ledgertoShow;

        DB::beginTransaction();

        try {
            $ledgers = Ledger::all();
            foreach ($ledgers as $ledger) {
                # code...
                self::autoSetBalanceById($ledger->id);
            }

            $ledgertoShow = Ledger::whereIn('kind', ['BANK', 'CASH', 'WALLET'])->pluck('id')->toArray();
            DB::commit();
            return BalanceSnapshot::whereDate('created_at', Carbon::now())->whereIn('ledger_id', $ledgertoShow)
                ->with('ledger')->get();
        } catch (\Exception $ex) {
            DB::rollBack();
        }
    }

    public static function updateBalance(int $id, $opening, $closing)
    {
        $balance = BalanceSnapshot::whereDate('created_at', Carbon::now())
            ->with('ledger')
            ->where('ledger_id', $id)->first();

        if (!$balance) {
            BalanceSnapshot::create([
                'ledger_id' => $id,
                'opening' => $opening,
                'closing' => $closing
            ]);
        } else {
            $balance->opening = $opening;
            $balance->closing = $closing;
            $balance->save();
        }

        return $balance;
    }

    public static function autoSetBalanceById(int $ledger_id)
    {
        $balance = BalanceSnapshot::where('ledger_id', $ledger_id)
            ->whereDate('created_at', Carbon::now())
            ->first();
        if (empty($balance)) {
            $opening  = self::getLatestClosing($ledger_id);
        } else {
            $opening = $balance->opening;
        }
        $credit = self::reduceAmount($ledger_id);
        $debit = self::increaseAmount($ledger_id);
        $closing = $opening - $credit + $debit;
        $ledger = self::updateBalance($ledger_id, $opening, $closing);
        return $ledger;
    }

    public static function getLatestClosing(int $ledger_id)
    {
        $balance  = BalanceSnapshot::where('ledger_id', $ledger_id)
            ->orderBy('id', 'desc')->first();
        if (empty($balance)) {
            return 0;
        }
        return $balance->closing;
    }

    private static function reduceAmount(int $ledger_id)
    {
        $creditAmount = Voucher::where('cr', $ledger_id)
            ->whereDate('created_at', Carbon::now())
            ->sum('amount');
        return $creditAmount;
    }

    private static function increaseAmount(int $ledger_id)
    {
        $debitAmount = Voucher::where('dr', $ledger_id)
            ->whereDate('created_at', Carbon::now())
            ->sum('amount');
        return $debitAmount;
    }

    public static function getValidationRules($uniqueTitle = false, $idRequired = false)
    {
        $rules = self::$validationRules;

        if ($uniqueTitle) {
            array_push($rules['title'], 'unique:App\Models\Ledger');
        }

        if ($idRequired) {
            array_push($rules['id'], 'required');
        } else {
            unset($rules['id']);
        }

        return $rules;
    }

    public function __construct()
    {
    }
}
