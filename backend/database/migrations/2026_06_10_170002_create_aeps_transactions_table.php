<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Shared AEPS transactions table used by every AEPS provider flow
 * (Noble, demo-provider-aeps, ...). Each row is one attempt at a
 * transaction with a given provider.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('aeps_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            // Client-supplied idempotency / reference id for the txn.
            $table->string('transaction_id')->index();

            $table->string('provider')->default('demo-provider-aeps');
            $table->string('tran_type', 8);                  // CW | BE | MS | AP
            $table->decimal('amount', 12, 2)->default(0);    // 0 for non-CW

            $table->string('mobile_number', 15)->nullable();
            $table->string('aadhaar_number', 16)->nullable(); // masked at app layer; never log raw

            $table->string('status')->default('pending');    // pending | success | failed
            $table->string('rrn')->nullable();               // provider reference number
            $table->string('provider_txn_id')->nullable();
            $table->string('message')->nullable();
            $table->json('provider_response')->nullable();

            $table->timestamps();

            $table->unique(['provider', 'transaction_id']);  // idempotency per provider
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aeps_transactions');
    }
};
