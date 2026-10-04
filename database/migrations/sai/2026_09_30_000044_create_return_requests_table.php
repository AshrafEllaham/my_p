<?php

use App\Enums\ReturnResolutionTypeEnum;
use App\Enums\ReturnStatusEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // يخزن طلبات الاسترجاع أو الاستبدال وقرار المتجر والمبلغ المعتمد.
        Schema::create('return_requests', function (Blueprint $table): void {
            $table->id();
            $table->uuid('public_id')->unique();
            $table->foreignId('order_id')->unique()->constrained('orders')->restrictOnDelete();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('store_id')->constrained('users')->restrictOnDelete();
            $table->string('status')->default(ReturnStatusEnum::PendingReview->value)->index();
            $table->string('reason', 100);
            $table->text('details');
            $table->decimal('requested_amount', 14, 2);
            $table->decimal('approved_amount', 14, 2)->nullable();
            $table->string('resolution_type')->nullable();
            $table->text('merchant_note')->nullable();
            $table->timestamp('decided_at')->nullable();
            $table->timestamp('product_received_at')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();

            $table->index(['store_id', 'status', 'created_at']);
            $table->index(['user_id', 'status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('return_requests');
    }
};
