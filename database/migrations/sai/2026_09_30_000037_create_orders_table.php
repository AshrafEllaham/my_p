<?php

use App\Enums\OrderStatusEnum;
use App\Enums\PaymentStatusEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // يخزن الطلب الرئيسي وحالته والمبالغ وبيانات الاستلام ونسخة السياسة.
        Schema::create('orders', function (Blueprint $table): void {
            $table->id();
            $table->uuid('public_id')->unique();
            $table->string('number', 32)->unique();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('store_id')->constrained('stores')->restrictOnDelete();
            $table->foreignId('coupon_id')->nullable()->constrained('coupons')->nullOnDelete();
            $table->foreignId('store_policy_version_id')->constrained('store_policy_versions')->restrictOnDelete();
            $table->enum('status', OrderStatusEnum::values())->default(OrderStatusEnum::PendingStore->value)->index();
            $table->enum('payment_status', PaymentStatusEnum::values())->default(PaymentStatusEnum::Pending->value)->index();
            $table->decimal('subtotal', 14, 2);
            $table->decimal('discount_amount', 14, 2)->default(0);
            $table->decimal('total_amount', 14, 2);
            $table->decimal('platform_fee_amount', 14, 2)->default(0);
            $table->decimal('merchant_net_amount', 14, 2)->default(0);
            $table->char('currency', 3)->default('EGP');
            $table->json('pickup_branch_snapshot');
            $table->json('policy_snapshot');
            $table->uuid('pickup_qr_token')->nullable()->unique();
            $table->string('pickup_code_hash')->nullable();
            $table->timestamp('pickup_expires_at')->nullable()->index();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('preparing_at')->nullable();
            $table->timestamp('ready_at')->nullable();
            $table->timestamp('customer_confirmation_requested_at')->nullable();
            $table->timestamp('completed_at')->nullable()->index();
            $table->timestamp('cancelled_at')->nullable();
            $table->text('cancellation_reason')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status', 'created_at']);
            $table->index(['store_id', 'status', 'created_at']);
            $table->index(['payment_status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
