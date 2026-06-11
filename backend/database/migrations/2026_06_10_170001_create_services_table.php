<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Catalogue of services/packages a user may be entitled to.
 *
 * The provider-style flow does a "package/service check" against this table
 * using a service slug (e.g. `demo-aeps-withdrawal`) before processing a
 * transaction. Only active services are allowed through.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('provider')->nullable();          // e.g. demo-provider-aeps
            $table->string('type')->default('aeps');         // aeps | bbps | dmt ...
            $table->boolean('is_active')->default(true);
            $table->json('meta')->nullable();                // limits, charge-slab keys, etc.
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};
