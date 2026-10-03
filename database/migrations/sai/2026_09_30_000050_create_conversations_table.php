<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // يخزن المحادثة المباشرة بين مستخدم ومتجر وسياق المنتج أو الطلب.
        Schema::create('conversations', function (Blueprint $table): void {
            $table->id();
            $table->uuid('public_id')->unique();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('store_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();
            $table->foreignId('order_id')->nullable()->constrained('orders')->nullOnDelete();
            $table->timestamp('last_message_at')->nullable()->index();
            $table->timestamp('user_read_at')->nullable();
            $table->timestamp('store_read_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'store_id']);
            $table->index(['user_id', 'last_message_at']);
            $table->index(['store_id', 'last_message_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('conversations');
    }
};
