<?php

use App\Enums\RequestStatusEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // يتابع طلبات المستخدم للحصول على نسخة من بيانات حسابه.
        Schema::create('account_data_requests', function (Blueprint $table): void {
            $table->id();
            $table->uuid('public_id')->unique();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('generated_document_id')->nullable()->constrained('generated_documents')->nullOnDelete();
            $table->string('status')->default(RequestStatusEnum::Pending->value)->index();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('account_data_requests');
    }
};
