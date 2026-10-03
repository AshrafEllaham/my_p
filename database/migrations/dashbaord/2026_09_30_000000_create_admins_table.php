<?php

use App\Enums\AdminTypeEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // يخزن حسابات مشرفي لوحة التحكم وصلاحية الدخول وحالة الحساب.
        Schema::create('admins', function (Blueprint $table): void {
            $table->id();
            $table->unsignedTinyInteger('admin_type')->default(AdminTypeEnum::Admin->value)->index();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('phone', 32)->nullable()->unique();
            $table->string('password');
            $table->boolean('is_active')->default(true);
            $table->boolean('is_blocked')->default(false);
            $table->foreignId('main_user_id')->nullable()->constrained('admins')->nullOnDelete();
            $table->timestamp('last_login_at')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admins');
    }
};
