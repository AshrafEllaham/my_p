<?php

use App\Enums\ProductReportReasonEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // يخزن بلاغات المستخدمين عن معلومات المنتجات أو مخالفتها.
        Schema::create('product_reports', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->enum('reason', ProductReportReasonEnum::values());
            $table->text('details')->nullable();
            $table->timestamp('resolved_at')->nullable()->index();
            $table->timestamps();

            $table->unique(['user_id', 'product_id', 'reason']);
            $table->index(['product_id', 'resolved_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_reports');
    }
};
