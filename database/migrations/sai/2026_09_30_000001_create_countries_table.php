<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // يخزن الدول المدعومة وأكوادها ومفاتيح الاتصال وحالة التفعيل.
        Schema::create('countries', function (Blueprint $table): void {
            $table->id();
            $table->string('code', 3)->unique();
            $table->string('phone_code', 10)->nullable();
            $table->string('flag', 50)->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('countries');
    }
};
