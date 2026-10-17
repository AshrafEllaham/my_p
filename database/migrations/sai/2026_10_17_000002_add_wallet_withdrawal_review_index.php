<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('wallet_withdrawal_requests', function (Blueprint $table): void {
            // يسرّع قائمة طلبات السحب المعلّقة المرتبة من الأحدث.
            $table->index(['status', 'created_at', 'id'], 'wallet_withdrawal_status_created_id_idx');
        });
    }

    public function down(): void
    {
        Schema::table('wallet_withdrawal_requests', function (Blueprint $table): void {
            $table->dropIndex('wallet_withdrawal_status_created_id_idx');
        });
    }
};
