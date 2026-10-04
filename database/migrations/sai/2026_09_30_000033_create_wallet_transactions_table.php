<?php

use App\Enums\WalletTransactionStatusEnum;
use App\Enums\WalletTransactionTypeEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // يسجل الحركات المالية على المحافظ مع الرصيد قبل وبعد ومنع التكرار.
        Schema::create('wallet_transactions', function (Blueprint $table): void {
            $table->id();
            $table->uuid('public_id')->unique(); // معرف عام فريد يمكن استخدامه في واجهات برمجة التطبيقات أو الروابط العامة.
            $table->foreignId('wallet_id')->constrained('wallets')->restrictOnDelete();
            $table->foreignId('counterparty_wallet_id')->nullable()->constrained('wallets')->nullOnDelete(); // يخزن المحفظة المقابلة في حالة التحويل بين المحافظ.
            $table->string('type')->index();
            $table->string('status')
                ->default(WalletTransactionStatusEnum::Pending->value)
                ->index();
            $table->decimal('amount', 14, 2);
            $table->decimal('balance_before', 14, 2);
            $table->decimal('balance_after', 14, 2);
            $table->char('currency', 3)->default('EGP');
            $table->nullableMorphs('reference'); // يخزن مرجعًا اختياريًا مرتبطًا بالحركة، مثل طلب شراء أو سحب أو إيداع.
            $table->string('idempotency_key')->unique(); // مفتاح عدم التكرار لضمان أن نفس العملية لا تتم معالجتها أكثر من مرة.
            $table->string('provider_reference')->nullable()->unique(); // يخزن مرجعًا فريدًا من مزود الدفع أو النظام الخارجي، إذا كان متاحًا.
            $table->json('metadata')->nullable(); // يخزن بيانات إضافية اختيارية مرتبطة بالحركة، مثل تفاصيل الدفع أو معلومات المستخدم.
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['wallet_id', 'status', 'created_at']);
            $table->index(['wallet_id', 'type', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wallet_transactions');
    }
};
