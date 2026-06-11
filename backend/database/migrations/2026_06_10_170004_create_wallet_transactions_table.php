<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Immutable ledger of every wallet movement. balance_after lets us
 * audit/replay the running balance. This is also where commission and
 * charge lines would be recorded (see NOTE-commission-charges.md).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wallet_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wallet_id')->constrained()->cascadeOnDelete();

            $table->string('type');                          // credit | debit
            $table->decimal('amount', 14, 2);
            $table->decimal('balance_after', 14, 2);

            $table->string('reference_type')->nullable();    // e.g. App\Models\AepsTransaction
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->string('remarks')->nullable();

            $table->timestamps();

            $table->index(['reference_type', 'reference_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wallet_transactions');
    }
};
