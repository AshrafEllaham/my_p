<?php

use App\Enums\SettlementStatusEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // يخزن طلبات تسوية أرباح المتجر وتحويلها إلى حسابه البنكي.
        Schema::create('settlement_requests', function (Blueprint $table): void {
            $table->id();
            $table->uuid('public_id')->unique();
            $table->foreignId('store_id')->constrained('stores')->restrictOnDelete();
            $table->foreignId('wallet_id')->constrained('wallets')->restrictOnDelete();
            $table->foreignId('store_bank_account_id')->constrained('store_bank_accounts')->restrictOnDelete();
            $table->enum('status', SettlementStatusEnum::values())->default(SettlementStatusEnum::Pending->value)->index();
            $table->decimal('gross_amount', 14, 2); // يخزن المبلغ الإجمالي قبل خصم أي رسوم أو عمولات.
            $table->decimal('fee_amount', 14, 2)->default(0); // يخزن مبلغ الرسوم أو العمولة المحتسبة.
            $table->decimal('net_amount', 14, 2); // يخزن المبلغ الصافي بعد خصم أي رسوم أو عمولات.
            $table->char('currency', 3)->default('EGP');
            $table->string('idempotency_key')->unique();
            $table->string('bank_reference')->nullable()->unique();
            $table->text('review_note')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();

            $table->index(['store_id', 'status', 'created_at']);
            $table->index(['wallet_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('settlement_requests');
    }
};
