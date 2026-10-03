<?php

use App\Enums\ProductStatusEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // يخزن بيانات المنتج التي يدخلها التاجر مع السعر والمخزون وحالة النشر.
        Schema::create('products', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('category_id')->constrained('categories')->restrictOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('status', ProductStatusEnum::values())->default(ProductStatusEnum::Draft->value)->index();
            $table->decimal('price', 12, 2); // يخزن سعر المنتج الحالي الذي يراه العملاء.
            $table->decimal('original_price', 12, 2)->nullable(); // يخزن السعر الأصلي للمنتج قبل أي خصم أو تخفيض.
            $table->unsignedInteger('stock_quantity')->default(0); // يخزن كمية المخزون المتاحة للمنتج.
            $table->unsignedInteger('low_stock_threshold')->default(5); // يخزن الحد الأدنى من المخزون الذي يثير تنبيه المخزون المنخفض.
            $table->boolean('is_featured')->default(false)->index(); // يخزن ما إذا كان المنتج مميزًا للعرض في الصفحات الرئيسية أو القوائم الخاصة.
            $table->timestamp('published_at')->nullable()->index();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['user_id', 'slug']);
            $table->unique(['user_id', 'sku']);
            $table->index(['user_id', 'status', 'updated_at']);
            $table->index(['category_id', 'status', 'is_featured']);
            $table->index(['user_id', 'stock_quantity']);
            $table->index(['status', 'published_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
