<?php

namespace App\Services\DemoProviderAeps;

use App\Models\AepsTransaction;
use App\Services\Support\ApiLogger;
use Illuminate\Support\Str;

/**
 * Client for the (simulated) demo AEPS provider.
 *
 * In a real integration this is where you would build the signed/encrypted
 * request, POST it to the provider over HTTP (Laravel's Http client), and
 * map the raw response onto a {@see ProviderResult}. Here we simulate a
 * deterministic response so the flow is runnable end-to-end offline.
 *
 * The outbound request + response are always written to api_logs via
 * {@see ApiLogger} (with sensitive fields masked).
 */
class DemoAepsProvider
{
    private const SERVICE = 'demo-aeps-withdrawal';
    private const ENDPOINT = 'https://demo-provider.example/api/v1/aeps/transaction';

    public function __construct(private readonly ApiLogger $logger) {}

    public function process(AepsTransaction $txn): ProviderResult
    {
        // --- Build the outbound request (what a real provider would receive) ---
        $request = [
            'client_ref'    => $txn->transaction_id,
            'tran_type'     => $txn->tran_type,
            'amount'        => (float) $txn->amount,
            'mobile'        => $txn->mobile_number,
            'aadhaarNumber' => $txn->aadhaar_number, // masked by ApiLogger on write
            'timestamp'     => now()->toIso8601String(),
        ];

        // --- Simulate the provider's decision -------------------------------
        // Demo rule: amounts that are a multiple of 13 are declined, so the
        // failure path is easy to exercise. Everything else succeeds.
        $declined = $txn->tran_type === 'CW' && ((int) $txn->amount % 13 === 0);

        $response = $declined
            ? [
                'status'     => 'FAILED',
                'statusCode' => 'P02',
                'message'    => 'Transaction declined by issuing bank',
                'rrn'        => null,
                'providerTxnId' => null,
            ]
            : [
                'status'        => 'SUCCESS',
                'statusCode'    => '00',
                'message'       => 'Transaction successful',
                'rrn'           => (string) random_int(100000000000, 999999999999),
                'providerTxnId' => 'DEMO' . strtoupper(Str::random(10)),
            ];

        $httpStatus = $declined ? 402 : 200;

        // --- Log request/response for this provider call --------------------
        $this->logger->log(
            service: self::SERVICE,
            direction: 'outbound',
            endpoint: self::ENDPOINT,
            method: 'POST',
            reference: $txn->transaction_id,
            request: $request,
            response: $response,
            statusCode: $httpStatus,
        );

        return new ProviderResult(
            success: $response['status'] === 'SUCCESS',
            message: $response['message'],
            rrn: $response['rrn'],
            providerTxnId: $response['providerTxnId'],
            raw: $response,
            statusCode: $httpStatus,
        );
    }
}
