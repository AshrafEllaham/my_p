<?php

use App\Enums\AccountTypeEnum;
use App\Enums\UserStatusEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // يخزن الحسابات الأساسية وبيانات تسجيل الدخول ونوع الحساب والموقع والحالة.
        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->string('name')->nullable();
            $table->string('email')->unique();
            $table->string('phone_code', 5)->nullable();
            $table->string('phone', 32)->nullable();
            $table->string('password')->nullable();
            $table->enum('account_type', AccountTypeEnum::values())->nullable()->index();
            $table->enum('status', UserStatusEnum::values())->default(UserStatusEnum::PendingVerification->value)->index();

            $table->foreignId('country_id')->nullable()->constrained('countries')->nullOnDelete();
            $table->foreignId('governorate_id')->nullable()->constrained('governorates')->nullOnDelete();
            $table->foreignId('city_id')->nullable()->constrained('cities')->nullOnDelete();
            $table->string('address_line')->nullable(); //
            $table->string('avatar')->nullable(); // يخزن رابط الصورة الرمزية للمستخدم.
            $table->string('preferred_locale', 5)->default('ar'); // يخزن اللغة المفضلة للمستخدم (مثل 'ar' للعربية أو 'en' للإنجليزية).
            $table->timestamp('last_login_at')->nullable(); // يخزن آخر وقت تسجيل دخول للمستخدم.
            $table->string('social_id')->nullable(); // يخزن معرف المستخدم من مزود تسجيل الدخول الاجتماعي (مثل Google أو Apple).
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['account_type', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
