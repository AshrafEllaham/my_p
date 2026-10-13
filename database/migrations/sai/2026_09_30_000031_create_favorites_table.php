<?php

use App\Enums\FavoriteTypeEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('favorites', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->enum('type', array_map(fn (FavoriteTypeEnum $type): string => $type->value, FavoriteTypeEnum::cases()));
            $table->unsignedBigInteger('favoriteable_id');
            $table->timestamps();

            $table->unique(['user_id', 'type', 'favoriteable_id']);
            $table->index(['type', 'favoriteable_id', 'created_at']);
        });

        DB::table('favorites')->insertUsing(
            ['user_id', 'type', 'favoriteable_id', 'created_at', 'updated_at'],
            DB::table('product_favorites')->select('user_id', DB::raw("'".FavoriteTypeEnum::Product->value."'"), 'product_id', 'created_at', 'updated_at'),
        );
        DB::table('favorites')->insertUsing(
            ['user_id', 'type', 'favoriteable_id', 'created_at', 'updated_at'],
            DB::table('store_favorites')->select('user_id', DB::raw("'".FavoriteTypeEnum::Store->value."'"), 'store_id', 'created_at', 'updated_at'),
        );

        Schema::dropIfExists('product_favorites');
        Schema::dropIfExists('store_favorites');
    }

    public function down(): void
    {
        Schema::create('product_favorites', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['user_id', 'product_id']);
            $table->index(['product_id', 'created_at']);
        });
        Schema::create('store_favorites', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('store_id')->constrained('stores')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['user_id', 'store_id']);
            $table->index(['store_id', 'created_at']);
        });

        DB::table('product_favorites')->insertUsing(
            ['user_id', 'product_id', 'created_at', 'updated_at'],
            DB::table('favorites')->where('type', FavoriteTypeEnum::Product->value)->select('user_id', 'favoriteable_id', 'created_at', 'updated_at'),
        );
        DB::table('store_favorites')->insertUsing(
            ['user_id', 'store_id', 'created_at', 'updated_at'],
            DB::table('favorites')->where('type', FavoriteTypeEnum::Store->value)->select('user_id', 'favoriteable_id', 'created_at', 'updated_at'),
        );

        Schema::dropIfExists('favorites');
    }
};
