<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\ServiceUnavailableException;
use App\Http\Controllers\Controller;
use App\Http\Requests\DemoAepsTransactionRequest;
use App\Models\User;
use App\Services\DemoProviderAeps\DemoAepsService;
use App\Services\Support\ApiLogger;
use App\Services\WalletService;
use Illuminate\Http\JsonResponse;

class DemoAepsController extends Controller
{
    public function __construct(
        private readonly DemoAepsService $service,
        private readonly WalletService $wallet,
        private readonly ApiLogger $logger,
    ) {}

    /**
     * POST /api/demo-aeps/transaction
     */
    public function transaction(DemoAepsTransactionRequest $request): JsonResponse
    {
        $data = $request->validated();

        // Resolve the acting user. In production this is $request->user();
        // for the demo we accept an optional userId, else fall back to the
        // first seeded user.
        $userId = $data['userId'] ?? optional($request->user())->id ?? User::min('id');

        // Inbound API request/response logging.
        $this->logger->log(
            service: DemoAepsService::SERVICE_SLUG,
            direction: 'inbound',
            endpoint: $request->path(),
            method: $request->method(),
            reference: $data['transactionId'],
            request: $request->except(['aadhaarNumber']) + ['aadhaarNumber' => $data['aadhaarNumber']],
            response: ['status' => 'received'],
            statusCode: 200,
        );

        try {
            $txn = $this->service->handle((int) $userId, $data);
        } catch (ServiceUnavailableException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'data'    => null,
            ], 403);
        }

        $isSuccess = $txn->status === 'success';

        return response()->json([
            'success' => $isSuccess,
            'message' => $txn->message ?? ($isSuccess ? 'Transaction successful' : 'Transaction failed'),
            'data'    => [
                'transactionId'  => $txn->transaction_id,
                'tranType'       => $txn->tran_type,
                'status'         => $txn->status,
                'amount'         => (float) $txn->amount,
                'rrn'            => $txn->rrn,
                'providerTxnId'  => $txn->provider_txn_id,
                'mobileNumber'   => $txn->mobile_number,
                'aadhaarNumber'  => $txn->maskedAadhaar(),
                // Only meaningful for a successful CW; harmless otherwise.
                'walletBalance'  => $this->wallet->balance((int) $userId, 'aeps'),
            ],
        ], $isSuccess ? 200 : 422);
    }
}
