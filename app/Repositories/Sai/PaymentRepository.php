<?php

namespace App\Repositories\Sai;

use App\Enums\PaymentMethodEnum;
use App\Enums\PaymentStatusEnum;
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

    /** @return array{id: int, order_id: int, status: PaymentStatusEnum, amount: string, metadata: array<string, mixed>|null}|null */
    public function orderPaymentSnapshotByIdempotencyKeyForUpdate(string $key): ?array
    {
        $payment = $this->query()
            ->select(['id', 'order_id', 'status', 'amount', 'metadata'])
            ->where('idempotency_key', $key)
            ->lockForUpdate()
            ->first();

        if ($payment === null) {
            return null;
        }

        return [
            'id' => (int) $payment->getKey(),
            'order_id' => (int) $payment->order_id,
            'status' => $payment->status,
            'amount' => (string) $payment->amount,
            'metadata' => is_array($payment->metadata) ? $payment->metadata : null,
        ];
    }

    /** @return array{id: int, status: PaymentStatusEnum, method: PaymentMethodEnum, amount: string, wallet_transaction_id: int|null}|null */
    public function orderPaymentForUpdate(int $orderId): ?array
    {
        $payment = $this->query()
            ->select(['id', 'order_id', 'status', 'method', 'amount', 'currency', 'wallet_transaction_id'])
            ->where('order_id', $orderId)
            ->lockForUpdate()
            ->first();

        return $payment === null ? null : $this->paymentSnapshot($payment);
    }

    public function createOrderPayment(array $data): int
    {
        return (int) $this->store($data)->getKey();
    }

    public function updateOrderPayment(int $paymentId, array $data): void
    {
        $payment = $this->findForUpdateOrFail($paymentId);
        $payment->fill($data);
        $payment->save();
    }

    public function findForUpdateOrFail(int|string $id): Payment
    {
        return $this->query()->lockForUpdate()->findOrFail($id);
    }

    /** @return array{id: int, status: PaymentStatusEnum, method: PaymentMethodEnum, amount: string, currency: string, wallet_transaction_id: int|null} */
    public function topUpSnapshotForUpdate(int|string $id): array
    {
        return $this->paymentSnapshot($this->findForUpdateOrFail($id));
    }

    /** @return array{id: int, status: PaymentStatusEnum, method: PaymentMethodEnum, amount: string, currency: string, wallet_transaction_id: int|null}|null */
    public function topUpByIdempotencyKeyForUpdate(string $key): ?array
    {
        $payment = $this->query()
            ->where('idempotency_key', $key)
            ->lockForUpdate()
            ->first();

        return $payment === null ? null : $this->paymentSnapshot($payment);
    }

    /** @param array<string, mixed> $data
     * @return array{id: int, status: PaymentStatusEnum, method: PaymentMethodEnum, amount: string, currency: string, wallet_transaction_id: int|null}
     */
    public function createTopUpRecord(array $data): array
    {
        return $this->paymentSnapshot($this->createRecord($data));
    }

    /** @param array<string, mixed> $data */
    public function updateTopUpRecordById(int|string $id, array $data): void
    {
        $payment = $this->findForUpdateOrFail($id);
        $payment->fill($data);
        $payment->save();
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

    /** @return array{id: int, status: PaymentStatusEnum, method: PaymentMethodEnum, amount: string, currency: string, wallet_transaction_id: int|null} */
    private function paymentSnapshot(Payment $payment): array
    {
        return [
            'id' => (int) $payment->getKey(),
            'status' => $payment->status,
            'method' => $payment->method,
            'amount' => (string) $payment->amount,
            'currency' => (string) $payment->currency,
            'wallet_transaction_id' => $payment->wallet_transaction_id === null
                ? null
                : (int) $payment->wallet_transaction_id,
        ];
    }
}
