<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // يخزن اختيارات المستخدم لتفعيل أو إيقاف كل نوع من الإشعارات.
        Schema::create('notification_preferences', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->boolean('orders_enabled')->default(true);
            $table->boolean('pickup_enabled')->default(true);
            $table->boolean('returns_enabled')->default(true);
            $table->boolean('chats_enabled')->default(true);
            $table->boolean('offers_enabled')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_preferences');
    }
};
