<?php

use App\Enums\ReturnStatusEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // يسجل تغيرات حالة طلب الاسترجاع ومن نفذها.
        Schema::create('return_status_histories', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('return_request_id')->constrained('return_requests')->cascadeOnDelete();
            $table->enum('from_status', ReturnStatusEnum::values())->nullable();
            $table->enum('to_status', ReturnStatusEnum::values());
            $table->nullableMorphs('actor');
            $table->text('note')->nullable();
            $table->timestamps();

            $table->index(['return_request_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('return_status_histories');
    }
};
