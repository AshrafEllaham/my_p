<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // يخزن أسماء المحافظات المترجمة لكل لغة مدعومة.
        Schema::create('governorate_translations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('governorate_id')->constrained('governorates')->cascadeOnDelete();
            $table->string('locale', 5)->index();
            $table->string('name');
            $table->unique(['governorate_id', 'locale']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('governorate_translations');
    }
};
