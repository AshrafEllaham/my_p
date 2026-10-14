<?php

use App\Enums\UserReportTypeEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_reports', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('type')->comment(UserReportTypeEnum::Store->value.', '.UserReportTypeEnum::Product->value);
            $table->unsignedBigInteger('item_id');
            $table->foreignId('report_reason_id')->constrained('report_reasons')->restrictOnDelete();
            $table->timestamps();

            $table->unique(['user_id', 'type', 'item_id', 'report_reason_id'], 'user_reports_unique_submission');
            $table->index(['type', 'item_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_reports');
    }
};
