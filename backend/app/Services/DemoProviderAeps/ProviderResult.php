<?php

namespace App\Services\DemoProviderAeps;

/**
 * Normalised result returned by the demo provider client. Every provider
 * integration should map its raw response onto a shape like this so the
 * orchestrator stays provider-agnostic.
 */
class ProviderResult
{
    public function __construct(
        public readonly bool $success,
        public readonly string $message,
        public readonly ?string $rrn = null,
        public readonly ?string $providerTxnId = null,
        public readonly array $raw = [],
        public readonly int $statusCode = 200,
    ) {}
}
