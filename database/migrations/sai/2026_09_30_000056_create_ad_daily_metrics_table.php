<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // يجمع مؤشرات أداء الإعلان اليومية من مشاهدات ونقرات وطلبات.
        Schema::create('ad_daily_metrics', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('ad_id')->constrained('ads')->cascadeOnDelete();
            $table->date('metric_date');
            $table->unsignedBigInteger('impressions')->default(0);
            $table->unsignedBigInteger('clicks')->default(0);
            $table->unsignedBigInteger('chats_started')->default(0);
            $table->unsignedBigInteger('orders_attributed')->default(0);
            $table->decimal('revenue_attributed', 14, 2)->default(0);
            $table->timestamps();

            $table->unique(['ad_id', 'metric_date']);
            $table->index(['metric_date', 'ad_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ad_daily_metrics');
    }
};
