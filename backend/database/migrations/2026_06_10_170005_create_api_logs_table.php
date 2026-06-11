<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Request/response audit log for provider integrations. One row per
 * outbound provider call (and optionally per inbound API hit). Sensitive
 * fields (aadhaar, biometric) are masked before being written here.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('api_logs', function (Blueprint $table) {
            $table->id();
            $table->string('service')->nullable();           // demo-aeps-withdrawal
            $table->string('direction')->default('outbound'); // inbound | outbound
            $table->string('endpoint')->nullable();
            $table->string('method', 10)->nullable();
            $table->string('reference')->nullable();          // transaction_id
            $table->json('request_payload')->nullable();
            $table->json('response_payload')->nullable();
            $table->integer('status_code')->nullable();
            $table->timestamps();

            $table->index(['service', 'reference']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('api_logs');
    }
};
