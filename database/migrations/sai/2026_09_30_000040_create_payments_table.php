<?php

use App\Enums\PaymentMethodEnum;
use App\Enums\PaymentStatusEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // يسجل محاولة دفع الطلب وحالتها ومرجع حركة المحفظة.
        Schema::create('payments', function (Blueprint $table): void {
            $table->id();
            $table->uuid('public_id')->unique();
            $table->foreignId('order_id')->constrained('orders')->restrictOnDelete();
            $table->foreignId('wallet_transaction_id')->nullable()->constrained('wallet_transactions')->nullOnDelete();
            $table->string('status')->default(PaymentStatusEnum::Pending->value)->index();
            $table->string('method')->default(PaymentMethodEnum::Wallet->value);
            $table->decimal('amount', 14, 2);
            $table->char('currency', 3)->default('EGP');
            $table->string('idempotency_key')->unique();
            $table->string('provider_reference')->nullable()->unique();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();

            $table->index(['order_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
