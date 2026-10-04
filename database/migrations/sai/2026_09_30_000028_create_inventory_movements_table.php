<?php

use App\Enums\InventoryMovementReasonEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // يسجل كل زيادة أو خصم في المخزون مع السبب والرصيد الناتج.
        Schema::create('inventory_movements', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->integer('quantity_change');
            $table->unsignedInteger('quantity_after');
            $table->string('reason');
            $table->nullableMorphs('reference');
            $table->nullableMorphs('actor');
            $table->text('note')->nullable();
            $table->timestamps();

            $table->index(['product_id', 'created_at']);
            $table->index(['product_id', 'reason', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_movements');
    }
};
