<?php

use App\Enums\TeamMemberStatusEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // يربط موظفي المتجر بأدوارهم وحالة دعوتهم أو وصولهم.
        Schema::create('store_team_members', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('store_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('store_role_id')->constrained('store_roles')->restrictOnDelete();
            $table->enum('status', TeamMemberStatusEnum::values())->default(TeamMemberStatusEnum::Invited->value)->index();
            $table->foreignId('invited_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('invited_at')->nullable();
            $table->timestamp('joined_at')->nullable();
            $table->timestamps();

            $table->unique(['store_id', 'user_id']);
            $table->index(['store_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('store_team_members');
    }
};
