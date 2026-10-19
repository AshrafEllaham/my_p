<?php

namespace App\Services\Sai;

use App\Repositories\Sai\OrderReceiptRepository;
use App\Repositories\Sai\OrderRepository;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class UserOrderReadService
{
    public function __construct(
        private readonly OrderRepository $orders,
        private readonly OrderReceiptRepository $receipts,
    ) {}

    /** @param array<string, mixed> $filters */
    public function list(int $userId, array $filters): Builder
    {
        return $this->orders->listForUser($userId, $filters);
    }

    public function find(int $userId, int|string $orderId): Model
    {
        return $this->orders->findForUser($orderId, $userId);
    }

    public function receipt(int $userId, int|string $orderId): Model
    {
        return $this->receipts->findForUserOrder((int) $orderId, $userId);
    }
}
