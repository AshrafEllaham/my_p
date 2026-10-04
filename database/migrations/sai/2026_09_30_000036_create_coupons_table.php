<?php

use App\Enums\CouponDiscountTypeEnum;
use App\Enums\CouponStatusEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // يخزن كوبونات المتجر وقيمة الخصم وصلاحيتها وحدود الاستخدام.
        Schema::create('coupons', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('store_id')->constrained('stores')->cascadeOnDelete();
            $table->string('code', 32);
            $table->string('discount_type');
            $table->decimal('discount_value', 12, 2);
            $table->decimal('minimum_order_amount', 12, 2)->default(0);
            $table->decimal('maximum_discount_amount', 12, 2)->nullable();
            $table->unsignedInteger('max_uses')->nullable();
            $table->unsignedInteger('max_uses_per_user')->default(1);
            $table->unsignedInteger('uses_count')->default(0);
            $table->string('status')->default(CouponStatusEnum::Active->value)->index();
            $table->timestamp('starts_at')->nullable()->index();
            $table->timestamp('expires_at')->nullable()->index();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['store_id', 'code']);
            $table->index(['store_id', 'status', 'expires_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('coupons');
    }
};
