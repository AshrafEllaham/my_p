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
            $table->uuid('public_id')->unique(); // معرف عام فريد يمكن استخدامه في واجهات برمجة التطبيقات أو الروابط العامة.
            $table->string('number', 32)->unique(); // يخزن رقم الطلب الفريد الذي يمكن عرضه للمستخدمين والمتاجر.
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('store_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('coupon_id')->nullable()->constrained('coupons')->nullOnDelete();
            $table->foreignId('store_policy_version_id')->constrained('store_policy_versions')->restrictOnDelete();
            $table->enum('status', OrderStatusEnum::values())->default(OrderStatusEnum::PendingStore->value)->index();
            $table->enum('payment_status', PaymentStatusEnum::values())->default(PaymentStatusEnum::Pending->value)->index();
            $table->decimal('subtotal', 14, 2); // يخزن المبلغ الإجمالي للمنتجات قبل أي خصومات أو رسوم أو عمولات.
            $table->decimal('discount_amount', 14, 2)->default(0); // يخزن مبلغ الخصم المحتسب من الكوبون أو العروض الترويجية.
            $table->decimal('total_amount', 14, 2); // يخزن المبلغ الإجمالي النهائي بعد خصم أي خصومات وإضافة أي رسوم أو عمولات.
            $table->decimal('platform_fee_amount', 14, 2)->default(0); // يخزن مبلغ العمولة أو الرسوم التي تخصمها المنصة من إجمالي الطلب قبل تحويل الأرباح إلى المتجر.
            $table->decimal('merchant_net_amount', 14, 2)->default(0); // يخزن المبلغ الصافي الذي سيحصل عليه المتجر بعد خصم أي عمولات أو رسوم من إجمالي الطلب.s
            $table->char('currency', 3)->default('EGP');
            $table->json('policy_snapshot'); // يخزن نسخة من سياسة المتجر في وقت إنشاء الطلب، بحيث يمكن الرجوع إليها لاحقًا حتى لو تغيرت السياسة.
            $table->uuid('pickup_qr_token')->nullable()->unique(); // يخزن رمز QR فريد يمكن استخدامه للتحقق من استلام الطلب عند الاستلام من المتجر.
            $table->string('pickup_code_hash')->nullable(); // يخزن نسخة مشفرة من رمز الاستلام الذي يمكن استخدامه للتحقق من استلام الطلب عند الاستلام من المتجر.
            $table->timestamp('pickup_expires_at')->nullable()->index(); // يخزن وقت انتهاء صلاحية رمز الاستلام أو رمز QR، بحيث لا يمكن استخدامه بعد هذا الوقت.
            $table->timestamp('confirmed_at')->nullable(); // يخزن وقت تأكيد الطلب من قبل المتجر، بحيث يمكن تتبع مدة معالجة الطلب.
            $table->timestamp('preparing_at')->nullable(); // يخزن وقت بدء المتجر في تحضير الطلب، بحيث يمكن تتبع مدة التحضير.
            $table->timestamp('ready_at')->nullable(); // يخزن وقت جاهزية الطلب للاستلام أو التسليم، بحيث يمكن تتبع مدة الانتظار.
            $table->timestamp('customer_confirmation_requested_at')->nullable(); // يخزن وقت طلب تأكيد العميل لاستلام الطلب، بحيث يمكن تتبع مدة الانتظار لتأكيد العميل.
            $table->timestamp('completed_at')->nullable()->index(); // يخزن وقت اكتمال الطلب بعد استلامه من قبل العميل أو التسليم، بحيث يمكن تتبع مدة التسليم.
            $table->timestamp('cancelled_at')->nullable(); // يخزن وقت إلغاء الطلب من قبل المتجر أو العميل، بحيث يمكن تتبع مدة الإلغاء.
            $table->text('cancellation_reason')->nullable(); // يخزن سبب إلغاء الطلب من قبل المتجر أو العميل، بحيث يمكن معرفة سبب الإلغاء وتحسين الخدمة.
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
