<?php

use App\Enums\AdInteractionEventTypeEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // يسجل مشاهدات ونقرات الإعلانات ويمنع احتساب إعادة إرسال الحدث مرتين.
        Schema::create('ad_interaction_events', function (Blueprint $table): void {
            $table->id();
            $table->uuid('event_id')->unique();
            $table->foreignId('ad_id')->constrained('ads')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('type')->default(AdInteractionEventTypeEnum::Impression->value);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ad_interaction_events');
    }
};
