<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // يخزن إجابة واحدة لكل مستخدم في المسابقة ونتيجة مطابقتها.
        Schema::create('competition_entries', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('competition_id')->constrained('competitions')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->string('answer');
            $table->string('normalized_answer')->index();
            $table->boolean('is_matching')->nullable()->index();
            $table->timestamps();

            $table->unique(['competition_id', 'user_id']);
            $table->index(['competition_id', 'is_matching']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('competition_entries');
    }
};
