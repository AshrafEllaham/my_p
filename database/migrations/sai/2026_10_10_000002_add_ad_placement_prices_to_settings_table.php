<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table): void {
            $table->decimal('ad_home_price', 12, 2)->nullable()->after('instagram');
            $table->decimal('ad_category_price', 12, 2)->nullable()->after('ad_home_price');
        });
    }

    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table): void {
            $table->dropColumn(['ad_home_price', 'ad_category_price']);
        });
    }
};
