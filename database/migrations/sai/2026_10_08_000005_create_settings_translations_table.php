<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('settings_translations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('settings_id')->constrained('settings')->cascadeOnDelete();
            $table->string('locale', 5);
            $table->string('website_name');
            $table->text('about_app');
            $table->longText('privacy');
            $table->longText('terms_conditions');

            $table->unique(['settings_id', 'locale']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('settings_translations');
    }
};
