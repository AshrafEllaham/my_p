<?php

use App\Enums\GeneratedDocumentTypeEnum;
use App\Enums\RequestStatusEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // يخزن بيانات الملفات المولدة مثل التقارير والفواتير وكشوف التسوية.
        Schema::create('generated_documents', function (Blueprint $table): void {
            $table->id();
            $table->uuid('public_id')->unique();
            $table->morphs('owner');
            $table->nullableMorphs('source');
            $table->string('type')->index();
            $table->string('status')->default(RequestStatusEnum::Pending->value)->index();
            $table->string('title');
            $table->string('file_path')->nullable();
            $table->date('period_from')->nullable();
            $table->date('period_to')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('generated_at')->nullable();
            $table->timestamp('expires_at')->nullable()->index();
            $table->timestamps();

            $table->index(['owner_type', 'owner_id', 'type', 'created_at'], 'documents_owner_type_created_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('generated_documents');
    }
};
