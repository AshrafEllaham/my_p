<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // يحفظ نسخ سياسات الاستلام والاسترجاع والاستبدال لضمان التاريخية.
        Schema::create('store_policy_versions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedInteger('version');
            $table->unsignedSmallInteger('pickup_hold_hours')->default(24);
            $table->unsignedSmallInteger('return_window_days')->default(0);
            $table->boolean('returns_enabled')->default(false);
            $table->boolean('exchanges_enabled')->default(false);
            $table->boolean('cancellation_enabled')->default(true);
            $table->text('pickup_instructions')->nullable();
            $table->text('return_terms')->nullable();
            $table->text('exchange_terms')->nullable();
            $table->text('cancellation_terms')->nullable();
            $table->boolean('is_current')->default(true);
            $table->timestamp('effective_at')->index();
            $table->timestamps();

            $table->unique(['user_id', 'version']);
            $table->index(['user_id', 'is_current']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('store_policy_versions');
    }
};
