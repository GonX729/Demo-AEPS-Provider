<?php

namespace App\Services\DemoProviderAeps;

use App\Exceptions\ServiceUnavailableException;
use App\Models\AepsTransaction;
use App\Models\Service;
use App\Services\WalletService;
use Illuminate\Support\Facades\DB;

/**
 * Orchestrates a demo-provider-aeps transaction:
 *
 *   1. Package/service check (slug `demo-aeps-withdrawal` must be active).
 *   2. Persist the attempt in `aeps_transactions` (status = pending).
 *   3. Call the provider client (which logs request/response).
 *   4. Update the transaction with the provider outcome.
 *   5. If tran_type = CW and the provider succeeded, credit the AEPS wallet.
 *
 * Mirrors the shape of the existing Noble AEPS flow: a thin controller, a
 * provider client, and this service holding the business rules.
 */
class DemoAepsService
{
    public const SERVICE_SLUG = 'demo-aeps-withdrawal';

    public function __construct(
        private readonly DemoAepsProvider $provider,
        private readonly WalletService $wallet,
        private readonly \App\Services\CommissionService $commissionService,
    ) {}

    /**
     * @param  array{transactionId:string,tranType:string,amount?:float|null,mobileNumber:string,aadhaarNumber:string}  $data
     */
    public function handle(int $userId, array $data): AepsTransaction
    {
        $service = $this->assertServiceAvailable($userId);

        $txn = AepsTransaction::create([
            'user_id'        => $userId,
            'transaction_id' => $data['transactionId'],
            'provider'       => 'demo-provider-aeps',
            'tran_type'      => $data['tranType'],
            'amount'         => $data['tranType'] === 'CW' ? (float) $data['amount'] : 0,
            'mobile_number'  => $data['mobileNumber'],
            'aadhaar_number' => $data['aadhaarNumber'],
            'status'         => AepsTransaction::STATUS_PENDING,
        ]);

        $result = $this->provider->process($txn);

        // Persist provider outcome + (on success CW) credit the wallet atomically.
        return DB::transaction(function () use ($txn, $result, $userId, $service) {
            $txn->update([
                'status'            => $result->success
                    ? AepsTransaction::STATUS_SUCCESS
                    : AepsTransaction::STATUS_FAILED,
                'rrn'               => $result->rrn,
                'provider_txn_id'   => $result->providerTxnId,
                'message'           => $result->message,
                'provider_response' => $result->raw,
            ]);

            if ($result->success && $txn->tran_type === 'CW') {
                $meta = json_decode($service->meta, true) ?? [];
                $slabKey = $meta['charge_slab_key'] ?? null;

                if ($slabKey) {
                    $commissionAmount = $this->commissionService->calculate($slabKey, (float) $txn->amount);

                    if ($commissionAmount > 0) {
                        $this->wallet->credit(
                            userId: $userId,
                            amount: $commissionAmount,
                            type: 'aeps',
                            referenceType: AepsTransaction::class,
                            referenceId: $txn->id,
                            remarks: "AEPS CW Commission for txn {$txn->transaction_id}",
                        );
                    } elseif ($commissionAmount < 0) {
                        $this->wallet->credit(
                            userId: $userId,
                            amount: $commissionAmount, // negative amount deducts balance
                            type: 'aeps',
                            referenceType: AepsTransaction::class,
                            referenceId: $txn->id,
                            remarks: "AEPS CW Charge for txn {$txn->transaction_id}",
                        );
                    }
                }

                $this->wallet->credit(
                    userId: $userId,
                    amount: (float) $txn->amount,
                    type: 'aeps',
                    referenceType: AepsTransaction::class,
                    referenceId: $txn->id,
                    remarks: "AEPS CW settlement for txn {$txn->transaction_id}",
                );
            }

            return $txn->refresh();
        });
    }

    /**
     * Package/service check. Production version would also verify the user's
     * package grants this service; here we check the global service catalogue.
     */
    private function assertServiceAvailable(int $userId): Service
    {
        $service = Service::where('slug', self::SERVICE_SLUG)->first();

        if (! $service || ! $service->is_active) {
            throw new ServiceUnavailableException(
                "Service [" . self::SERVICE_SLUG . "] is not available or inactive."
            );
        }

        return $service;
    }
}
