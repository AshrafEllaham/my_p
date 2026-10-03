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
            $table->uuid('public_id')->unique();
            $table->foreignId('wallet_id')->constrained('wallets')->restrictOnDelete();
            $table->foreignId('counterparty_wallet_id')->nullable()->constrained('wallets')->nullOnDelete();
            $table->enum('type', WalletTransactionTypeEnum::values())->index();
            $table->enum('status', WalletTransactionStatusEnum::values())
                ->default(WalletTransactionStatusEnum::Pending->value)
                ->index();
            $table->decimal('amount', 14, 2);
            $table->decimal('balance_before', 14, 2);
            $table->decimal('balance_after', 14, 2);
            $table->char('currency', 3)->default('EGP');
            $table->nullableMorphs('reference');
            $table->string('idempotency_key')->unique();
            $table->string('provider_reference')->nullable()->unique();
            $table->json('metadata')->nullable();
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
