<?php

namespace App\Models\Sai;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\AccountTypeEnum;
use App\Enums\UserStatusEnum;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;

#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements JWTSubject
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, SoftDeletes;

    protected static function newFactory(): UserFactory
    {
        return UserFactory::new();
    }

    protected $fillable = [
        'name',
        'email',
        'phone_code',
        'phone',
        'password',
        'account_type',
        'status',
        'country_id',
        'governorate_id',
        'city_id',
        'address_line',
        'avatar',
        'preferred_locale',
        'last_login_at',
        'social_id',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'account_type' => AccountTypeEnum::class,
            'status' => UserStatusEnum::class,
            'last_login_at' => 'datetime',
        ];
    }

    /** Unique ID embedded in the JWT token. */
    public function getJWTIdentifier(): mixed
    {
        return $this->getKey();
    }

    /** Extra data attached to the JWT token payload. */
    public function getJWTCustomClaims(): array
    {
        return [];
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function governorate(): BelongsTo
    {
        return $this->belongsTo(Governorate::class);
    }

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    public function store(): HasOne
    {
        return $this->hasOne(Store::class, 'owner_id');
    }
}
