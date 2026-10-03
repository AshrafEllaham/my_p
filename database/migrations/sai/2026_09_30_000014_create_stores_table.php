<?php

use App\Enums\StoreStatusEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // يخزن الهوية التشغيلية والقانونية والموقع وحالة تفعيل كل متجر.
        Schema::create('stores', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('owner_id')->unique()->constrained('users')->restrictOnDelete();
            $table->foreignId('category_id')->constrained('categories')->restrictOnDelete(); // يخزن القسم الرئيسي الذي ينتمي إليه المتجر.
            $table->text('description')->nullable();
            $table->string('cover_image')->nullable(); // يخزن رابط صورة الغلاف للمتجر.
            $table->string('contact_phone', 32)->nullable(); // يخزن رقم الهاتف للتواصل مع المتجر.
            $table->string('contact_email')->nullable(); // يخزن البريد الإلكتروني للتواصل مع المتجر.
            $table->boolean('is_featured')->default(false)->index();
            $table->index(['is_featured']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stores');
    }
};
