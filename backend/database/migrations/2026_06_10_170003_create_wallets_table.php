<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Per-user wallets. A user can hold more than one wallet type
 * (e.g. `aeps`, `main`). Balance is kept as a decimal and only ever
 * mutated inside a DB transaction with a row lock (see WalletService).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wallets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('type')->default('aeps');         // aeps | main ...
            $table->decimal('balance', 14, 2)->default(0);
            $table->timestamps();

            $table->unique(['user_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wallets');
    }
};
