<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('faq_translations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('faq_id')->constrained('faqs')->cascadeOnDelete();
            $table->string('locale', 5);
            $table->string('question');
            $table->text('answer');

            $table->unique(['faq_id', 'locale']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('faq_translations');
    }
};
