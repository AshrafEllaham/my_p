<?php

namespace App\Models\Sai;

use App\Enums\ProductStatusEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'store_id',
        'category_id',
        'name',
        'description',
        'sku',
        'slug',
        'status',
        'price',
        'original_price',
        'discount_percentage',
        'discount_ends_at',
        'stock_quantity',
        'low_stock_threshold',
        'is_featured',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => ProductStatusEnum::class,
            'price' => 'decimal:2',
            'original_price' => 'decimal:2',
            'discount_percentage' => 'decimal:2',
            'discount_ends_at' => 'datetime',
            'stock_quantity' => 'integer',
            'low_stock_threshold' => 'integer',
            'is_featured' => 'boolean',
            'published_at' => 'datetime',
        ];
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(User::class, 'store_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function media(): HasMany
    {
        return $this->hasMany(ProductMedia::class)->orderByDesc('is_primary')->orderBy('id');
    }

    public function features(): HasMany
    {
        return $this->hasMany(ProductFeature::class)->orderBy('id');
    }
}
