<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // يسجل أثر العمليات الحساسة ومنفذها والقيم قبل التغيير وبعده.
        Schema::create('activity_logs', function (Blueprint $table): void {
            $table->id();
            $table->nullableMorphs('actor');
            $table->nullableMorphs('subject');
            $table->foreignId('store_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('event', 100)->index();
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();

            $table->index(['store_id', 'created_at']);
            $table->index(['store_id', 'event', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
