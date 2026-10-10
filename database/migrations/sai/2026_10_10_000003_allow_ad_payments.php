<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table): void {
            $table->foreignId('order_id')->nullable()->change();
            $table->foreignId('ad_id')->nullable()->after('order_id')->constrained('ads')->restrictOnDelete();
            $table->index(['ad_id', 'status']);
        });
    }

    public function down(): void
    {
        DB::table('payments')->whereNotNull('ad_id')->delete();

        Schema::table('payments', function (Blueprint $table): void {
            $table->dropIndex(['ad_id', 'status']);
            $table->dropConstrainedForeignId('ad_id');
            $table->foreignId('order_id')->nullable(false)->change();
        });
    }
};
