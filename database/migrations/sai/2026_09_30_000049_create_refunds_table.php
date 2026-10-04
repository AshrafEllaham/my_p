<?php

use App\Enums\WalletTransactionStatusEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // يسجل عمليات رد الأموال ويربطها بالطلب والنزاع أو الاسترجاع والمحفظة.
        Schema::create('refunds', function (Blueprint $table): void {
            $table->id();
            $table->uuid('public_id')->unique();
            $table->foreignId('order_id')->constrained('orders')->restrictOnDelete();
            $table->foreignId('payment_id')->nullable()->constrained('payments')->nullOnDelete();
            $table->foreignId('return_request_id')->nullable()->unique()->constrained('return_requests')->nullOnDelete();
            $table->foreignId('order_dispute_id')->nullable()->unique()->constrained('order_disputes')->nullOnDelete();
            $table->foreignId('wallet_transaction_id')->nullable()->unique()->constrained('wallet_transactions')->nullOnDelete();
            $table->string('status')
                ->default(WalletTransactionStatusEnum::Pending->value)
                ->index();
            $table->decimal('amount', 14, 2);
            $table->char('currency', 3)->default('EGP');
            $table->string('idempotency_key')->unique();
            $table->text('reason')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['order_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('refunds');
    }
};
