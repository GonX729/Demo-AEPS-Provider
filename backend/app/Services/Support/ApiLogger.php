<?php

namespace App\Services\Support;

use App\Models\ApiLog;

/**
 * Persists request/response pairs for provider integrations.
 *
 * Sensitive fields are masked here so raw PII/biometric data never lands
 * in the api_logs table.
 */
class ApiLogger
{
    /** Keys whose values must be masked before storage. */
    private const SENSITIVE = ['aadhaarNumber', 'aadhaar_number', 'biometric', 'pidData', 'piData'];

    public function log(
        string $service,
        string $direction,
        ?string $endpoint,
        ?string $method,
        ?string $reference,
        array $request,
        array $response,
        ?int $statusCode = null,
    ): ApiLog {
        return ApiLog::create([
            'service'          => $service,
            'direction'        => $direction,
            'endpoint'         => $endpoint,
            'method'           => $method,
            'reference'        => $reference,
            'request_payload'  => $this->mask($request),
            'response_payload' => $this->mask($response),
            'status_code'      => $statusCode,
        ]);
    }

    private function mask(array $payload): array
    {
        foreach ($payload as $key => $value) {
            if (is_array($value)) {
                $payload[$key] = $this->mask($value);
            } elseif (in_array($key, self::SENSITIVE, true) && is_string($value)) {
                $payload[$key] = strlen($value) > 4
                    ? str_repeat('X', strlen($value) - 4) . substr($value, -4)
                    : 'XXXX';
            }
        }

        return $payload;
    }
}
