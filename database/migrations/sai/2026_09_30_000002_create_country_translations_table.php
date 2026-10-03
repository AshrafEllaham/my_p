<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // يخزن أسماء الدول المترجمة لكل لغة مدعومة.
        Schema::create('country_translations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('country_id')->constrained('countries')->cascadeOnDelete();
            $table->string('locale', 5)->index();
            $table->string('name');
            $table->unique(['country_id', 'locale']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('country_translations');
    }
};
