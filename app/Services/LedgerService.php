<?php

namespace App\Services;

use App\Models\Ledger;
use App\Models\Voucher;
use Illuminate\Support\Carbon;
use App\Models\BalanceSnapShot;
use Illuminate\Support\Facades\DB;

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

    public static function takeCompleteBalanceSnapshot()
    {
        DB::beginTransaction();

        try {
            Ledger::select('id', 'balance')->each(function ($ledger) {
                $opening = BalanceSnapShot::where('ledger_id', $ledger->id)
                    ->orderBy('created_at', 'desc')
                    ->first()->balance;
                $credit = self::reduceAmount($ledger->id);
                $debit = self::increaseAmount($ledger->id);
                $closing = $opening - $credit + $debit;

                self::takeLedgerBalanceSnapshot($ledger->id, $opening, $closing);
            });
            DB::commit();
        } catch (\Exception $ex) {
            DB::rollBack();
            return response($ex);
        }
    }

    public static function takeLedgerBalanceSnapshot(int $ledger_id, float $opening, float $closing)
    {
        $row = BalanceSnapShot::where('ledger_id', $ledger_id)
            ->whereDate('created_at', Carbon::now())
            ->first();
        if (empty($row)) {
            BalanceSnapShot::new([
                'ledger_id' => $ledger_id,
                'opening' => $opening,
                'closing' => $closing
            ]);
            return;
        }

        $row->opening = $opening;
        $row->closing = $closing;
        $row->save();
        return;
    }

    public static function getValidationRules($uniqueTitle = false, $idRequired = false)
    {
        $rules = self::$validationRules;
        if ($uniqueTitle) {
            array_push($rules['title'], 'unique:App\Models\Ledger');
        }

        if ($idRequired) {
            array_push($rules['id'], 'required');
        }

        return $rules;
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

    public function __construct()
    {
    }
}
