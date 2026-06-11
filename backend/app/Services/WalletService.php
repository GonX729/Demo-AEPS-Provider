<?php

namespace App\Services;

use App\Models\Wallet;
use App\Models\WalletTransaction;
use Illuminate\Support\Facades\DB;

/**
 * All wallet mutations go through here so balance changes are atomic and
 * always paired with a ledger row.
 */
class WalletService
{
    /**
     * Credit a user's wallet of the given type and write a ledger entry.
     * The balance read + write happen under a row lock inside a single
     * DB transaction to avoid lost updates under concurrency.
     */
    public function credit(
        int $userId,
        float $amount,
        string $type = 'aeps',
        ?string $referenceType = null,
        ?int $referenceId = null,
        ?string $remarks = null,
    ): WalletTransaction {
        return DB::transaction(function () use ($userId, $amount, $type, $referenceType, $referenceId, $remarks) {
            $wallet = Wallet::query()
                ->where('user_id', $userId)
                ->where('type', $type)
                ->lockForUpdate()
                ->firstOrCreate(
                    ['user_id' => $userId, 'type' => $type],
                    ['balance' => 0],
                );

            $wallet->balance = (float) $wallet->balance + $amount;
            $wallet->save();

            return $wallet->transactions()->create([
                'type'           => WalletTransaction::TYPE_CREDIT,
                'amount'         => $amount,
                'balance_after'  => $wallet->balance,
                'reference_type' => $referenceType,
                'reference_id'   => $referenceId,
                'remarks'        => $remarks,
            ]);
        });
    }

    public function balance(int $userId, string $type = 'aeps'): float
    {
        return (float) (Wallet::query()
            ->where('user_id', $userId)
            ->where('type', $type)
            ->value('balance') ?? 0);
    }
}
