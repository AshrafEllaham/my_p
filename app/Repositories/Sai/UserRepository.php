<?php

namespace App\Repositories\Sai;

use App\Enums\SocialLoginProviderEnum;
use App\Enums\UserStatusEnum;
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

    public function profileForUser(int $userId): User
    {
        /** @var User $user */
        $user = $this->query()
            ->select([
                'id', 'name', 'email', 'phone_code', 'phone', 'avatar', 'country_id',
                'governorate_id', 'city_id', 'address_line', 'pickup_latitude',
                'pickup_longitude', 'account_type',
            ])
            ->with([
                'governorate:id,country_id',
                'governorate.translations:id,governorate_id,locale,name',
                'city:id,governorate_id',
                'city.translations:id,city_id,locale,name',
            ])
            ->findOrFail($userId);

        return $user;
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

    /** @return array{id: int, name: string|null, status: UserStatusEnum}|null */
    public function findWalletTransferUserByIdentity(string $identity): ?array
    {
        $identity = trim($identity);

        if (str_contains($identity, '@')) {
            $user = $this->query()
                ->select(['id', 'name', 'status'])
                ->where('email', mb_strtolower($identity))
                ->first();

            return $user === null ? null : $this->walletTransferUserSnapshot($user);
        }

        $digits = preg_replace('/\\D+/', '', $identity) ?? '';
        if ($digits === '') {
            return null;
        }

        $phoneCodes = $this->query()
            ->select('phone_code')
            ->whereNotNull('phone_code')
            ->distinct()
            ->pluck('phone_code');
        $phoneMatches = [];

        foreach ($phoneCodes as $phoneCode) {
            $callingCode = preg_replace('/\\D+/', '', (string) $phoneCode) ?? '';
            if ($callingCode === '' || ! str_starts_with($digits, $callingCode)) {
                continue;
            }

            $localNumber = substr($digits, strlen($callingCode));
            if ($localNumber === '') {
                continue;
            }

            $phoneValues = [$localNumber];
            $withoutTrunkPrefix = ltrim($localNumber, '0');
            if ($withoutTrunkPrefix !== '' && $withoutTrunkPrefix !== $localNumber) {
                $phoneValues[] = $withoutTrunkPrefix;
            } else {
                $phoneValues[] = '0'.$localNumber;
            }

            $phoneMatches[] = [
                'phone_code' => (string) $phoneCode,
                'phones' => array_values(array_unique($phoneValues)),
            ];
        }

        $users = $this->query()
            ->select(['id', 'name', 'status'])
            ->where(function (Builder $query) use ($phoneCodes, $digits, $phoneMatches): void {
                $query->where(function (Builder $localPhoneQuery) use ($phoneCodes, $digits): void {
                    $localPhoneQuery->where(function (Builder $codedQuery) use ($phoneCodes, $digits): void {
                        $codedQuery->whereIn('phone_code', $phoneCodes)
                            ->where('phone', $digits);
                    })->orWhere(function (Builder $uncodedQuery) use ($digits): void {
                        $uncodedQuery->whereNull('phone_code')
                            ->where('phone', $digits);
                    });
                });

                foreach ($phoneMatches as $match) {
                    $query->orWhere(function (Builder $phoneQuery) use ($match): void {
                        $phoneQuery->where('phone_code', $match['phone_code'])
                            ->whereIn('phone', $match['phones']);
                    });
                }
            })
            ->get()
            ->unique('id')
            ->values();

        if ($users->count() !== 1) {
            return null;
        }

        return $this->walletTransferUserSnapshot($users->first());
    }

    /** @return array{id: int, name: string|null, status: UserStatusEnum}|null */
    public function findWalletTransferUserById(int $userId): ?array
    {
        $user = $this->query()
            ->select(['id', 'name', 'status'])
            ->find($userId);

        return $user === null ? null : $this->walletTransferUserSnapshot($user);
    }

    /** @param array<int, int> $userIds
     * @return array<int, int>
     */
    public function lockOwnersForUpdate(array $userIds): array
    {
        $userIds = array_values(array_unique(array_map('intval', $userIds)));
        sort($userIds, SORT_NUMERIC);

        return $this->query()
            ->select('id')
            ->whereIn('id', $userIds)
            ->orderBy('id')
            ->lockForUpdate()
            ->pluck('id')
            ->map(static fn (int|string $id): int => (int) $id)
            ->all();
    }

    public function findTrashedByEmail(string $email): ?User
    {
        return $this->query()->onlyTrashed()->where('email', $email)->first();
    }

    public function findTrashedBySocialId(string $socialId): ?User
    {
        return $this->query()->onlyTrashed()->where('social_id', $socialId)->first();
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

    public function updatePassword(User $user, string $password): void
    {
        $user->forceFill(['password' => $password])->save();
    }

    public function updateStatus(User $user, UserStatusEnum $status): void
    {
        $user->forceFill(['status' => $status])->save();
    }

    public function lockForUpdate(int|string $id): User
    {
        /** @var User $user */
        $user = $this->query()->lockForUpdate()->findOrFail($id);

        return $user;
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
        if (array_key_exists('avatar', $data)) {
            $data['avatar'] = ImageHelper::upload($data['avatar'], 'users/avatars', $record->avatar);
        }
        $record->fill($data);
        $record->save();

        return $record;
    }

    public function deleteRecord(int|string $id): bool
    {
        return (bool) $this->findOrFail($id)->delete();
    }

    public function softDelete(User $user): bool
    {
        return (bool) $user->delete();
    }

    /** @return array{id: int, name: string|null, status: UserStatusEnum} */
    private function walletTransferUserSnapshot(User $user): array
    {
        return [
            'id' => (int) $user->getKey(),
            'name' => $user->name,
            'status' => $user->status,
        ];
    }
}
