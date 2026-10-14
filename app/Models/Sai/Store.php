<?php

namespace App\Models\Sai;

use App\Enums\FavoriteTypeEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Store extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'owner_id',
        'category_id',
        'description',
        'cover_image',
        'contact_phone',
        'contact_email',
        'latitude',
        'longitude',
        'is_featured',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'is_featured' => 'boolean',
        ];
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function favorites(): HasMany
    {
        return $this->hasMany(Favorite::class, 'favoriteable_id')->where('type', FavoriteTypeEnum::Store);
    }
}
