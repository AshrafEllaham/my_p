<?php

use App\Enums\PayoutMethodEnum;
use App\Enums\RequestStatusEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // يخزن طلبات سحب المستخدم ووسيلة التحويل وحالة المعالجة.
        Schema::create('wallet_withdrawal_requests', function (Blueprint $table): void {
            $table->id();
            $table->uuid('public_id')->unique();
            $table->foreignId('wallet_id')->constrained('wallets')->restrictOnDelete();
            $table->foreignId('wallet_transaction_id')->nullable()->unique()->constrained('wallet_transactions')->nullOnDelete(); // يخزن الحركة المالية المرتبطة بطلب السحب بعد معالجته، إذا تم إنشاؤه.
            $table->enum('method', PayoutMethodEnum::values()); // يخزن طريقة الدفع أو التحويل التي اختارها المستخدم لسحب الأموال، مثل التحويل البنكي أو المحافظ الإلكترونية.
            $table->string('destination'); // يخزن تفاصيل الوجهة التي اختارها المستخدم لاستلام الأموال، مثل رقم الحساب البنكي أو عنوان المحفظة الإلكترونية.
            $table->decimal('amount', 14, 2);
            $table->char('currency', 3)->default('EGP');
            $table->enum('status', RequestStatusEnum::values())->default(RequestStatusEnum::Pending->value)->index();
            $table->string('idempotency_key')->unique(); // مفتاح عدم التكرار لضمان أن نفس طلب السحب لا يتم معالجته أكثر من مرة.
            $table->text('review_note')->nullable(); // يخزن ملاحظات المراجعة أو التعليقات من المسؤول عند مراجعة طلب السحب، مثل سبب الرفض أو التعليمات الإضافية.
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();

            $table->index(['wallet_id', 'status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wallet_withdrawal_requests');
    }
};
