<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('report_reasons', function (Blueprint $table): void {
            $table->id();
            $table->timestamps();
        });

        Schema::create('report_reason_translations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('report_reason_id')->constrained('report_reasons')->cascadeOnDelete();
            $table->string('locale', 2);
            $table->string('title');
            $table->timestamps();

            $table->unique(['report_reason_id', 'locale']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('report_reason_translations');
        Schema::dropIfExists('report_reasons');
    }
};
