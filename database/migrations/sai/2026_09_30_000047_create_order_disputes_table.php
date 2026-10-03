<?php

use App\Enums\DisputeStatusEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // يخزن النزاعات التي توقف إتمام الطلب وقرار تسويتها.
        Schema::create('order_disputes', function (Blueprint $table): void {
            $table->id();
            $table->uuid('public_id')->unique();
            $table->foreignId('order_id')->unique()->constrained('orders')->restrictOnDelete();
            $table->foreignId('opened_by')->constrained('users')->restrictOnDelete();
            $table->foreignId('store_id')->constrained('stores')->restrictOnDelete();
            $table->enum('status', DisputeStatusEnum::values())->default(DisputeStatusEnum::Open->value)->index();
            $table->string('reason', 100);
            $table->text('details');
            $table->text('resolution_note')->nullable();
            $table->nullableMorphs('resolved_by');
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();

            $table->index(['store_id', 'status', 'created_at']);
            $table->index(['opened_by', 'status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_disputes');
    }
};
