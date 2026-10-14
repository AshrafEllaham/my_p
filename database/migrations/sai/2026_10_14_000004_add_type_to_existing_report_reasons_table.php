<?php

use App\Enums\UserReportTypeEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('report_reasons', function (Blueprint $table): void {
            $table->string('type')
                ->default(UserReportTypeEnum::Product->value)
                ->comment(UserReportTypeEnum::Store->value.', '.UserReportTypeEnum::Product->value)
                ->index()
                ->after('id');
        });
    }

    public function down(): void
    {
        Schema::table('report_reasons', function (Blueprint $table): void {
            $table->dropIndex(['type']);
            $table->dropColumn('type');
        });
    }
};
