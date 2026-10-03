<?php

use App\Enums\AdActionEnum;
use App\Enums\AdPlacementEnum;
use App\Enums\AdStatusEnum;
use App\Enums\MediaTypeEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // يخزن حملات المتجر الإعلانية وموضعها وجدولها وتكلفتها وحالتها.
        Schema::create('ads', function (Blueprint $table): void {
            $table->id();
            $table->uuid('public_id')->unique();
            $table->foreignId('store_id')->constrained('stores')->cascadeOnDelete();
            $table->foreignId('ad_package_id')->constrained('ad_packages')->restrictOnDelete();
            $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();
            $table->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->foreignId('wallet_transaction_id')->nullable()->unique()->constrained('wallet_transactions')->nullOnDelete();
            $table->string('title', 120);
            $table->string('action_label', 60)->nullable();
            $table->text('caption')->nullable();
            $table->enum('placement', AdPlacementEnum::values())->index(); // يخزن موضع الإعلان في التطبيق (على سبيل المثال: الصفحة الرئيسية، صفحة المنتج، صفحة الفئة).
            $table->enum('action', AdActionEnum::values()); // يخزن نوع الإجراء الذي يحدث عند النقر على الإعلان (على سبيل المثال: فتح صفحة المنتج، فتح صفحة الفئة، فتح رابط خارجي).
            $table->enum('media_type', MediaTypeEnum::values())->default(MediaTypeEnum::Image->value);
            $table->string('media_path');
            $table->enum('status', AdStatusEnum::values())->default(AdStatusEnum::Draft->value)->index();
            $table->decimal('cost', 12, 2);
            $table->char('currency', 3)->default('EGP');
            $table->timestamp('starts_at')->nullable()->index();
            $table->timestamp('ends_at')->nullable()->index();
            $table->text('rejection_reason')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['store_id', 'status', 'created_at']);
            $table->index(['placement', 'status', 'starts_at', 'ends_at'], 'ads_placement_schedule_idx');
            $table->index(['category_id', 'status', 'starts_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ads');
    }
};
