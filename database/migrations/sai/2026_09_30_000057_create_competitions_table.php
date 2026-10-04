<?php

use App\Enums\CompetitionStatusEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // يخزن مسابقات المتاجر وأسئلتها وجوائزها ونتيجة السحب النهائية.
        Schema::create('competitions', function (Blueprint $table): void {
            $table->id();
            $table->uuid('public_id')->unique();
            $table->foreignId('store_id')->constrained('stores')->cascadeOnDelete();
            $table->foreignId('winner_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('content_locale', 5)->default('ar');
            $table->string('title', 120);
            $table->text('question');
            $table->string('prize', 160);
            $table->string('image')->nullable();
            $table->string('status')->default(CompetitionStatusEnum::Active->value)->index();
            $table->string('correct_answer')->nullable();
            $table->string('normalized_correct_answer')->nullable()->index(); // يخزن نسخة من الإجابة الصحيحة بعد إزالة المسافات وعلامات التشكيل لتسهيل المطابقة مع إجابات المستخدمين.
            $table->timestamp('ends_at')->index();
            $table->timestamp('drawn_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['store_id', 'status', 'ends_at']);
            $table->index(['status', 'ends_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('competitions');
    }
};
