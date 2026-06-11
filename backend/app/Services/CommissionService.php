<?php

namespace App\Services;

use Illuminate\Support\Facades\Config;

class CommissionService
{
    /**
     * Calculates the commission or charge for a given amount using a slab key.
     * Returns a positive float for a commission (credit), or a negative float for a charge (debit).
     */
    public function calculate(string $slabKey, float $amount): float
    {
        $slabs = Config::get("charges.{$slabKey}");

        if (! $slabs) {
            // No slab found, default to 0 commission
            return 0.0;
        }

        foreach ($slabs as $slab) {
            if ($amount >= $slab['min_amount'] && $amount <= $slab['max_amount']) {
                $value = (float) $slab['value'];

                if ($slab['type'] === 'percent') {
                    // e.g., 0.2% of 2000 = (2000 * 0.2) / 100 = 4.0
                    return round(($amount * $value) / 100, 2);
                }

                // Flat commission/charge
                return round($value, 2);
            }
        }

        // Amount didn't fall into any slab, default to 0
        return 0.0;
    }
}
