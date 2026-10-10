<?php

namespace App\Repositories\Sai;

use App\Models\Sai\Payment;
use App\Repositories\MainRepository;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class PaymentRepository extends MainRepository
{
    public function __construct(Payment $model)
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

    public function findByIdempotencyKey(string $key): ?Payment
    {
        return $this->query()->where('idempotency_key', $key)->first();
    }

    public function findForUpdateOrFail(int|string $id): Payment
    {
        return $this->query()->lockForUpdate()->findOrFail($id);
    }

    public function findAdPayment(int $adId): ?Payment
    {
        return $this->query()->where('ad_id', $adId)->first();
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
    public function updatePayment(Payment $payment, array $data): Payment
    {
        $payment->fill($data);
        $payment->save();

        return $payment;
    }

    public function deleteRecord(int|string $id): bool
    {
        return (bool) $this->findOrFail($id)->delete();
    }
}
