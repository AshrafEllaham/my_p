<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const INDEX = 'wallet_transactions_wallet_type_created_id_idx';

    public function up(): void
    {
        Schema::table('wallet_transactions', function (Blueprint $table): void {
            $table->index(['wallet_id', 'type', 'created_at', 'id'], self::INDEX);
        });
    }

    public function down(): void
    {
        Schema::table('wallet_transactions', function (Blueprint $table): void {
            $table->dropIndex(self::INDEX);
        });
    }
};
