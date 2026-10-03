<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // يخزن الرسائل والأدلة المتبادلة داخل نزاع الطلب.
        Schema::create('order_dispute_messages', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('order_dispute_id')->constrained('order_disputes')->cascadeOnDelete();
            $table->morphs('sender');
            $table->text('body');
            $table->json('attachments')->nullable();
            $table->timestamps();

            $table->index(['order_dispute_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_dispute_messages');
    }
};
