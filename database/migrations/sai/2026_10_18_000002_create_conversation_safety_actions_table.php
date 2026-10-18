<?php

use App\Enums\ConversationSafetyActionTypeEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('conversation_safety_actions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('conversation_id')->constrained('conversations')->cascadeOnDelete();
            $table->foreignId('actor_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('target_id')->constrained('users')->restrictOnDelete();
            $table->string('action')->comment(ConversationSafetyActionTypeEnum::Report->value.', '.ConversationSafetyActionTypeEnum::Block->value);
            $table->string('reason', 120)->nullable();
            $table->text('details')->nullable();
            $table->timestamps();

            $table->unique(['conversation_id', 'actor_id', 'action'], 'conversation_safety_action_unique');
            $table->index(['target_id', 'action', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('conversation_safety_actions');
    }
};
