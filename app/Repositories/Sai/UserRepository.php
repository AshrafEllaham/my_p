<?php

namespace App\Repositories\Sai;

use App\Enums\SocialLoginProviderEnum;
use App\Helpers\ImageHelper;
use App\Models\Sai\SocialAccount;
use App\Models\Sai\User;
use App\Repositories\MainRepository;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class UserRepository extends MainRepository
{
    public function __construct(User $model)
    {
        $this->model = $model;
    }

    public function query(): Builder
    {
        return $this->getModel()->newQuery();
    }

    public function findOrFail(int|string $id): Model
    {
        return $this->query()->findOrFail($id);
    }

    /** @param array<string, mixed> $data */
    public function createRecord(array $data): Model
    {
        $record = $this->getModel()->newInstance();
        $record->fill($data);
        $record->save();

        return $record;
    }

    public function findByEmail(string $email): ?User
    {
        return $this->query()->where('email', $email)->first();
    }

    public function findByPhone(string $phoneCode, string $phone): ?User
    {
        return $this->query()
            ->where('phone_code', $phoneCode)
            ->where('phone', $phone)
            ->first();
    }

    public function findBySocialIdentity(SocialLoginProviderEnum $provider, string $socialId): ?User
    {
        return SocialAccount::query()
            ->where('provider', $provider)
            ->where('social_id', $socialId)
            ->with('user')
            ->first()?->user;
    }

    public function createSocialUser(array $userData, SocialLoginProviderEnum $provider, string $socialId, string $providerEmail): User
    {
        /** @var User $user */
        $user = $this->createRecord($userData);
        $user->socialAccounts()->create([
            'provider' => $provider,
            'social_id' => $socialId,
            'provider_email' => $providerEmail,
        ]);

        return $user;
    }

    public function attachSocialIdentity(User $user, SocialLoginProviderEnum $provider, string $socialId, string $providerEmail): void
    {
        $user->socialAccounts()->create([
            'provider' => $provider,
            'social_id' => $socialId,
            'provider_email' => $providerEmail,
        ]);
    }

    public function hasSocialAccounts(User $user): bool
    {
        return SocialAccount::query()->where('user_id', $user->getKey())->exists();
    }

    public function markLoggedIn(User $user): void
    {
        $user->forceFill(['last_login_at' => now()])->save();
    }

    public function updatePhone(User $user, string $phoneCode, string $phone): void
    {
        $user->forceFill([
            'phone_code' => $phoneCode,
            'phone' => $phone,
        ])->save();
    }

    /** @param array<string, mixed> $data */
    public function updateRecord(int|string $id, array $data): Model
    {
        $record = $this->findOrFail($id);
        $record->fill($data);
        $record->save();

        return $record;
    }

    /** @param array<string, mixed> $data */
    public function updateProfile(int|string $id, array $data): Model
    {
        $record = $this->findOrFail($id);
        $data['avatar'] = ImageHelper::upload($data['avatar'], 'users/avatars', $record->avatar);
        $record->fill($data);
        $record->save();

        return $record;
    }

    public function deleteRecord(int|string $id): bool
    {
        return (bool) $this->findOrFail($id)->delete();
    }
}
