<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // يخزن رصيد المحفظة المتاح والمعلق للمستخدم أو المتجر.
        Schema::create('wallets', function (Blueprint $table): void {
            $table->id();
            $table->morphs('owner');
            $table->char('currency', 3)->default('EGP');
            $table->decimal('available_balance', 14, 2)->default(0);
            $table->decimal('pending_balance', 14, 2)->default(0);
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();

            $table->unique(['owner_type', 'owner_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wallets');
    }
};
