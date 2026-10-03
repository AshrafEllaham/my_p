<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // يخزن أسماء باقات الإعلان وأوصافها المترجمة.
        Schema::create('ad_package_translations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('ad_package_id')->constrained('ad_packages')->cascadeOnDelete();
            $table->string('locale', 5)->index();
            $table->string('name');
            $table->text('description')->nullable();

            $table->unique(['ad_package_id', 'locale']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ad_package_translations');
    }
};
