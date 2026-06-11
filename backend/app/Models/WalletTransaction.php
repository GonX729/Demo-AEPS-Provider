<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WalletTransaction extends Model
{
    protected $fillable = [
        'wallet_id', 'type', 'amount', 'balance_after',
        'reference_type', 'reference_id', 'remarks',
    ];

    protected $casts = [
        'amount'        => 'decimal:2',
        'balance_after' => 'decimal:2',
    ];

    public const TYPE_CREDIT = 'credit';
    public const TYPE_DEBIT  = 'debit';

    public function wallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class);
    }
}
