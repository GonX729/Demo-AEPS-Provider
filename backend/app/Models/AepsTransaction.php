<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AepsTransaction extends Model
{
    protected $fillable = [
        'user_id', 'transaction_id', 'provider', 'tran_type', 'amount',
        'mobile_number', 'aadhaar_number', 'status', 'rrn',
        'provider_txn_id', 'message', 'provider_response',
    ];

    protected $casts = [
        'amount'            => 'decimal:2',
        'provider_response' => 'array',
    ];

    public const STATUS_PENDING = 'pending';
    public const STATUS_SUCCESS = 'success';
    public const STATUS_FAILED  = 'failed';

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** Mask the Aadhaar number for any API/JSON output. */
    public function maskedAadhaar(): ?string
    {
        if (! $this->aadhaar_number) {
            return null;
        }

        return 'XXXXXXXX' . substr($this->aadhaar_number, -4);
    }
}
