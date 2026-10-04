<?php

namespace App\Repositories\Sai;

use App\Enums\OtpPurposeEnum;
use App\Models\Sai\OneTimePassword;
use App\Repositories\MainRepository;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class OneTimePasswordRepository extends MainRepository
{
    public function __construct(OneTimePassword $model)
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

    public function expireUnconsumed(string $identity, OtpPurposeEnum $purpose): int
    {
        return $this->query()
            ->where('identity', $identity)
            ->where('purpose', $purpose->value)
            ->whereNull('consumed_at')
            ->update(['consumed_at' => now()]);
    }

    public function findLatestUsable(string $identity, OtpPurposeEnum $purpose, ?bool $verified = null): ?OneTimePassword
    {
        return $this->query()
            ->where('identity', $identity)
            ->where('purpose', $purpose->value)
            ->whereNull('consumed_at')
            ->where('expires_at', '>', now())
            ->when($verified === true, fn (Builder $query) => $query->whereNotNull('verified_at'))
            ->when($verified === false, fn (Builder $query) => $query->whereNull('verified_at'))
            ->latest('id')
            ->lockForUpdate()
            ->first();
    }

    public function markVerified(OneTimePassword $otp): void
    {
        $otp->forceFill(['verified_at' => now()])->save();
    }

    public function incrementAttempts(OneTimePassword $otp): void
    {
        $otp->increment('attempts');
    }

    public function consumeForUser(OneTimePassword $otp, int $userId): void
    {
        $otp->forceFill([
            'user_id' => $userId,
            'consumed_at' => now(),
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

    public function deleteRecord(int|string $id): bool
    {
        return (bool) $this->findOrFail($id)->delete();
    }
}
