<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // يخزن حسابات التسوية البنكية المعتمدة للمتجر.
        Schema::create('user_bank_accounts', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('account_holder_name');
            $table->string('iban', 64)->unique();
            $table->string('bank_name')->nullable();
            $table->boolean('is_default')->default(true);
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'is_default']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_bank_accounts');
    }
};
