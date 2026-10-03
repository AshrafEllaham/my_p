<?php

use App\Enums\StoreStatusEnum;
use App\Enums\UserStatusEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // يسجل طلبات تفعيل المتجر وقرارات المراجعة وتوقيتها.
        Schema::create('store_verification_submissions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('store_id')->constrained('users')->cascadeOnDelete();
            $table->enum('status', UserStatusEnum::values())->default(UserStatusEnum::PendingVerification->value)->index();
            $table->text('review_note')->nullable();
            $table->timestamp('submitted_at')->index();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();

            $table->index(['store_id', 'submitted_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('store_verification_submissions');
    }
};
