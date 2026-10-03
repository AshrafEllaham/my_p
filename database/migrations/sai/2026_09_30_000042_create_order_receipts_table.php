<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // يخزن لقطة إيصال الطلب ورقم الإيصال وملف PDF عند توليده.
        Schema::create('order_receipts', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('order_id')->unique()->constrained('orders')->restrictOnDelete();
            $table->string('receipt_number', 40)->unique();
            $table->json('snapshot');
            $table->string('pdf_path')->nullable();
            $table->timestamp('issued_at')->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_receipts');
    }
};
