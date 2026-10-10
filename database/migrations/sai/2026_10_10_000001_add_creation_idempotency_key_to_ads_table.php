<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ads', function (Blueprint $table): void {
            $table->uuid('creation_idempotency_key')->nullable();
            $table->unique(['store_id', 'creation_idempotency_key'], 'ads_store_creation_idempotency_unique');
        });
    }

    public function down(): void
    {
        Schema::table('ads', function (Blueprint $table): void {
            $table->dropUnique('ads_store_creation_idempotency_unique');
            $table->dropColumn('creation_idempotency_key');
        });
    }
};
