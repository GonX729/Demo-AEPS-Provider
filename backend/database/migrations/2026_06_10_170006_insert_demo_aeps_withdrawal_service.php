<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Seeds the `demo-aeps-withdrawal` service row. The demo-provider-aeps
 * flow checks for this slug (and is_active = true) before processing a
 * cash-withdrawal transaction.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('services')->updateOrInsert(
            ['slug' => 'demo-aeps-withdrawal'],
            [
                'name'       => 'Demo AEPS Cash Withdrawal',
                'provider'   => 'demo-provider-aeps',
                'type'       => 'aeps',
                'is_active'  => true,
                'meta'       => json_encode([
                    'min_amount'      => 100,
                    'max_amount'      => 10000,
                    // Key used to look up the commission/charge slab for this
                    // service. The slab itself lives in config/charges or a
                    // charge_slabs table — see NOTE-commission-charges.md.
                    'charge_slab_key' => 'aeps_cw_default',
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        );
    }

    public function down(): void
    {
        DB::table('services')->where('slug', 'demo-aeps-withdrawal')->delete();
    }
};
