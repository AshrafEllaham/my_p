<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // يخزن الصور والأدلة المرفقة بطلب الاسترجاع.
        Schema::create('return_request_media', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('return_request_id')->constrained('return_requests')->cascadeOnDelete();
            $table->string('path');
            $table->string('mime_type', 100)->nullable();
            $table->unsignedTinyInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['return_request_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('return_request_media');
    }
};
