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
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->char('currency', 3)->default('EGP');
            $table->decimal('available_balance', 14, 2)->default(0); // يخزن الرصيد المتاح الذي يمكن للمستخدم أو المتجر استخدامه في عمليات الشراء أو السحب.
            $table->decimal('pending_balance', 14, 2)->default(0); // يخزن الرصيد المعلق الذي لم يتم تأكيده بعد، مثل المبالغ التي تم إيداعها ولكن لم يتم التحقق منها أو المبالغ التي تم خصمها ولكن لم يتم تسويتها بعد.
            $table->boolean('is_active')->default(true)->index(); // يخزن ما إذا كانت المحفظة نشطة أو غير نشطة.
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wallets');
    }
};
