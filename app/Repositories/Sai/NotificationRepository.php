<?php

namespace App\Repositories\Sai;

use App\Models\Sai\Notification;
use App\Models\Sai\User;
use App\Repositories\MainRepository;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

class NotificationRepository extends MainRepository
{
    public function __construct(Notification $model)
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

    public function paginateForUser(int $userId, bool $unreadOnly, int $perPage): LengthAwarePaginator
    {
        return $this->query()
            ->where('notifiable_type', User::class)
            ->where('notifiable_id', $userId)
            ->when($unreadOnly, fn (Builder $query): Builder => $query->whereNull('read_at'))
            ->latest()
            ->paginate($perPage);
    }

    public function markAsReadForUser(int $userId, string $notificationId): Model
    {
        $notification = $this->query()
            ->where('notifiable_type', User::class)
            ->where('notifiable_id', $userId)
            ->findOrFail($notificationId);

        if ($notification->read_at === null) {
            $notification->fill(['read_at' => now()]);
            $notification->save();
        }

        return $notification;
    }

    public function markAllAsReadForUser(int $userId): int
    {
        return $this->query()
            ->where('notifiable_type', User::class)
            ->where('notifiable_id', $userId)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }

    public function deleteAllForUser(int $userId): int
    {
        return $this->query()
            ->where('notifiable_type', User::class)
            ->where('notifiable_id', $userId)
            ->delete();
    }
}
