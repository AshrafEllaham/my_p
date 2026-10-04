<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('one_time_passwords', function (Blueprint $table): void {
            $table->timestamp('verified_at')->nullable()->after('expires_at');
        });
    }

    public function down(): void
    {
        Schema::table('one_time_passwords', function (Blueprint $table): void {
            $table->dropColumn('verified_at');
        });
    }
};
