<?php

namespace App\Services;

use App\Models\Voucher;
use App\Models\BalanceSnapshot;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class VoucherService
{

    public static function select(int $id, string $from, string $to)
    {
        $voucher = NULL;
        if ($from === $to) {
            $voucher = Voucher::whereDate('created_at', $from);
        } else {
            $to = Carbon::createFromFormat('Y-m-d', $to)->addDay(1);
            $voucher = Voucher::whereBetween('created_at', [$from, $to]);
        }
        $data = $voucher
            ->where(function ($query) use ($id) {
                $query->where('cr', $id)
                    ->orWhere('dr', $id);
            })->with(['creditor', 'debtor'])
            ->orderBy('created_at', 'ASC')
            ->get();

        $opening = BalanceSnapshot::where('ledger_id', $id)
            ->whereDate('created_at', $from)
            ->pluck('opening')->pop();

        if (is_null($opening)) {
            $opening = 0;
        }

        return ['openingBalance' => $opening, 'vouchers' => $data];
    }

    public static function create($data)
    {
        if ($data['cr'] == $data['dr']) {
            // If Creditor and Debtor are Same
            return response('CR and DR Same', 400);
        }
        $user_id = Auth::user()->id;

        if (!key_exists('immutable', $data)) {
            $data['immutable'] = false;
        }

        $voucher = Voucher::create([
            'cr' => $data['cr'],
            'dr' => $data['dr'],
            'narration' => $data['narration'],
            'amount' => $data['amount'],
            'user_id' => $user_id,
            'immutable' => $data['immutable']
        ]);

        return $voucher;
    }

    public static function update($voucher_data)
    {
        $voucher = Voucher::findOrFail($voucher_data['id']);

        if ($voucher->immutable) {
            return response('Voucher is Immutable', 403);
        }

        $dateDiff = Carbon::now()
            ->diffInDays(
                Carbon::parse($voucher->created_at)
            );

        if ($dateDiff > 0) {
            return response('Cant Edit Older Vouchers', 403);
        }

        DB::beginTransaction();

        try {
            $voucher->cr = $voucher_data['cr'];
            $voucher->dr = $voucher_data['dr'];
            $voucher->amount = $voucher_data['amount'];
            $voucher->narration = $voucher_data['narration'];
            $voucher->save();

            DB::commit();
        } catch (\Exception $ex) {
            DB::rollBack();
            return response($ex, 500);
        }

        return $voucher;
    }

    public function delete(int $id)
    {
        Voucher::findOrFail($id)->softDeletes();
        return response('Deleted Successfully', 204);
    }

    public static function dayBook(string $date)
    {
        return Voucher::select(
            DB::raw("
                TRUNCATE(sum(vouchers.amount), 2) as amount
            "),
            DB::raw("cr, dr")
        )->whereDate('created_at', $date)
            ->with(['creditor', 'debtor'])
            ->groupBy(['cr', 'dr'])->get();
    }


    public function __construct()
    {
    }
}
