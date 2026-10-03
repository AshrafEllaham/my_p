<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // يخزن باقات الإعلان ومدتها وسعرها وحالة إتاحتها.
        Schema::create('ad_packages', function (Blueprint $table): void {
            $table->id();
            $table->string('code')->unique();
            $table->unsignedSmallInteger('duration_days');
            $table->decimal('price', 12, 2);
            $table->char('currency', 3)->default('EGP');
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ad_packages');
    }
};
