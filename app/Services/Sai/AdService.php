<?php

namespace App\Services\Sai;

use App\Enums\AdPlacementEnum;
use App\Enums\AdStatusEnum;
use App\Enums\AdSubmissionActionEnum;
use App\Enums\PaymentMethodEnum;
use App\Enums\PaymentOperationTableEnum;
use App\Enums\PaymentStatusEnum;
use App\Enums\WalletTransactionStatusEnum;
use App\Enums\WalletTransactionTypeEnum;
use App\Helpers\ImageHelper;
use App\Models\Sai\AdPackage;
use App\Repositories\Sai\AdRepository;
use App\Repositories\Sai\PaymentRepository;
use App\Repositories\Sai\SettingsRepository;
use App\Repositories\Sai\WalletRepository;
use App\Repositories\Sai\WalletTransactionRepository;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Throwable;

class AdService
{
    public function __construct(
        private readonly AdRepository $repository,
        private readonly WalletRepository $walletRepository,
        private readonly WalletTransactionRepository $walletTransactionRepository,
        private readonly SettingsRepository $settingsRepository,
        private readonly PaymentRepository $paymentRepository,
    ) {}

    private function assertStoreOwner(int $ownerId): int
    {
        if (! $this->repository->isStoreOwner($ownerId)) {
            throw ValidationException::withMessages([
                'account_type' => [__('messages.profile.store_type_required')],
            ]);
        }

        return $this->repository->storeIdForOwner($ownerId);
    }

    public function query(): Builder
    {
        return $this->repository->query();
    }

    public function find(int|string $id): Model
    {
        return $this->repository->findOrFail($id);
    }

    /** @param array<string, mixed> $data */
    public function create(array $data): Model
    {
        return $this->repository->createRecord($data);
    }

    /** @param array<string, mixed> $data */
    public function update(int|string $id, array $data): Model
    {
        return $this->repository->updateRecord($id, $data);
    }

    public function delete(int|string $id): bool
    {
        return $this->repository->deleteRecord($id);
    }

    public function listForStore(int $ownerId, array $filters): Builder
    {
        $storeId = $this->assertStoreOwner($ownerId);

        return $this->repository->listForStore($storeId, $filters);
    }

    /** @return array{total_impressions_count: int, total_impressions_this_month_count: int, total_new_customers_count: int, total_active_campaigns_count: int} */
    public function summary(int $ownerId): array
    {
        $storeId = $this->assertStoreOwner($ownerId);

        return $this->repository->summaryForStore($storeId);
    }

    public function detailsForStore(int $ownerId, int|string $adId): Model
    {
        $storeId = $this->assertStoreOwner($ownerId);

        return $this->repository->findDetailsForStore($adId, $storeId);
    }

    /** @return Collection<int, AdPackage> */
    public function activePackagesForStore(): Collection
    {
        return $this->repository->activePackages();
    }

    /** @return array{balance: string, currency: ?string} */
    public function walletBalanceForStore(int $ownerId): array
    {
        $this->assertStoreOwner($ownerId);
        $wallet = $this->walletRepository->activeForOwner($ownerId);

        return [
            'balance' => (string) ($wallet?->available_balance ?? '0.00'),
            'currency' => $wallet?->currency,
        ];
    }

    /** @return array{ad: ?Model, payment: ?Model} */
    public function createForStore(int $ownerId, array $data): array
    {
        $storeId = $this->assertStoreOwner($ownerId);
        $this->assertProductBelongsToStore($ownerId, $data['product_id'] ?? null);
        $package = $this->repository->activePackage((int) $data['ad_package_id']);
        if ($package === null) {
            throw ValidationException::withMessages([
                'ad_package_id' => [__('messages.validation.store_ads.ad_package_id.exists')],
            ]);
        }
        $submissionAction = AdSubmissionActionEnum::from($data['submission_action']);
        $creationKey = $data['idempotency_key'] ?? null;
        if ($creationKey !== null) {
            $existingAd = $this->repository->findForStoreByCreationKey($creationKey, $storeId);
            if ($existingAd !== null) {
                $existingPayment = $this->paymentRepository->findAdPayment((int) $existingAd->getKey());

                return [
                    'ad' => $existingAd,
                    'payment' => $existingPayment?->status === PaymentStatusEnum::Pending ? $existingPayment : null,
                ];
            }

            $existingPayment = $this->paymentRepository->findByIdempotencyKey($this->creationPaymentKey($creationKey));
            if ($existingPayment?->status === PaymentStatusEnum::Pending) {
                return ['ad' => null, 'payment' => $existingPayment];
            }
        }

        $media = $data['media'];
        $paymentMethod = PaymentMethodEnum::tryFrom($data['payment_method'] ?? PaymentMethodEnum::Wallet->value)
            ?? PaymentMethodEnum::Wallet;
        unset($data['media'], $data['ad_package_id'], $data['submission_action'], $data['idempotency_key'], $data['payment_method']);
        $mediaPath = ImageHelper::upload($media, 'ads', null, $data['media_type']);

        try {
            return DB::transaction(function () use ($ownerId, $storeId, $package, $data, $submissionAction, $paymentMethod, $creationKey, $mediaPath): array {
                $adAttributes = $data + [
                    'public_id' => (string) Str::uuid(),
                    'store_id' => $storeId,
                    'ad_package_id' => $package->getKey(),
                    'creation_idempotency_key' => $creationKey,
                    'media_path' => $mediaPath,
                    'status' => AdStatusEnum::Draft,
                    'cost' => $this->costForPlacement(AdPlacementEnum::from($data['placement']), $package->price),
                    'currency' => $package->currency,
                ];

                if (
                    $submissionAction === AdSubmissionActionEnum::SubmitForReview
                    && $paymentMethod === PaymentMethodEnum::Online
                    && $this->moneyToCents((string) $adAttributes['cost']) > 0
                ) {
                    $adAttributes['status'] = AdStatusEnum::PendingReview;
                    $paymentKey = $this->creationPaymentKey((string) $creationKey);
                    $payment = $this->paymentRepository->findByIdempotencyKey($paymentKey);
                    $paymentData = [
                        'ad_id' => null,
                        'status' => PaymentStatusEnum::Pending,
                        'method' => PaymentMethodEnum::Online,
                        'amount' => $adAttributes['cost'],
                        'currency' => $adAttributes['currency'],
                        'idempotency_key' => $paymentKey,
                        'metadata' => ['kind' => 'new_ad', 'ad_attributes' => $adAttributes],
                    ];

                    if ($payment === null) {
                        $payment = $this->paymentRepository->createRecord([
                            'public_id' => (string) Str::uuid(),
                        ] + $paymentData);
                    } else {
                        $payment = $this->paymentRepository->updatePayment($payment, $paymentData);
                    }

                    return ['ad' => null, 'payment' => $payment];
                }

                $ad = $this->repository->createForStore($adAttributes);

                return $submissionAction === AdSubmissionActionEnum::SubmitForReview
                    ? $this->processSubmitForReview($ownerId, $storeId, $ad, $paymentMethod)
                    : ['ad' => $ad, 'payment' => null];
            });
        } catch (Throwable $exception) {
            Storage::disk('public')->delete($mediaPath);

            if ($exception instanceof QueryException && $creationKey !== null) {
                $existingPayment = $this->paymentRepository->findByIdempotencyKey($this->creationPaymentKey($creationKey));
                if ($existingPayment !== null) {
                    return ['ad' => null, 'payment' => $existingPayment];
                }

                $existingAd = $this->repository->findForStoreByCreationKey($creationKey, $storeId);
                if ($existingAd !== null) {
                    return ['ad' => $existingAd, 'payment' => null];
                }
            }

            throw $exception;
        }
    }

    /** @return array{ad: Model, payment: ?Model} */
    public function submitForReview(int $ownerId, int|string $adId, PaymentMethodEnum $paymentMethod): array
    {
        $storeId = $this->assertStoreOwner($ownerId);

        return DB::transaction(function () use ($ownerId, $storeId, $adId, $paymentMethod): array {
            $ad = $this->repository->findForStoreForUpdate($adId, $storeId);

            return $this->processSubmitForReview($ownerId, $storeId, $ad, $paymentMethod);
        });
    }

    /** @return array{ad: Model, payment: ?Model} */
    private function processSubmitForReview(int $ownerId, int $storeId, Model $ad, PaymentMethodEnum $paymentMethod): array
    {
        if ($ad->status === AdStatusEnum::PendingReview) {
            return ['ad' => $ad, 'payment' => null];
        }

        if ($ad->status !== AdStatusEnum::Draft) {
            throw ValidationException::withMessages([
                'status' => [__('messages.ads.submit_unavailable')],
            ]);
        }

        $costInCents = $this->moneyToCents((string) $ad->cost);

        if ($costInCents > 0 && $paymentMethod === PaymentMethodEnum::Online) {
            $idempotencyKey = 'ad-payment-'.$ad->public_id;
            $payment = $this->paymentRepository->findByIdempotencyKey($idempotencyKey);

            if ($payment !== null && $payment->method !== PaymentMethodEnum::Online) {
                throw ValidationException::withMessages([
                    'payment_method' => [__('messages.ads.payment_method_locked')],
                ]);
            }

            if ($payment?->status === PaymentStatusEnum::Paid) {
                $submittedAd = $this->repository->updateForStore($ad->getKey(), $storeId, [
                    'status' => AdStatusEnum::PendingReview,
                ]);

                return ['ad' => $submittedAd, 'payment' => null];
            }

            $paymentData = [
                'status' => PaymentStatusEnum::Pending,
                'method' => PaymentMethodEnum::Online,
                'amount' => $ad->cost,
                'currency' => $ad->currency,
                'ad_id' => $ad->getKey(),
                'provider_reference' => null,
                'paid_at' => null,
                'metadata' => [
                    'kind' => 'existing_ad',
                    'ad_id' => $ad->getKey(),
                    'store_id' => $storeId,
                    'media_path' => $ad->media_path,
                ],
            ];

            if ($payment === null) {
                $payment = $this->paymentRepository->createRecord([
                    'public_id' => (string) Str::uuid(),
                    'idempotency_key' => $idempotencyKey,
                ] + $paymentData);
            } else {
                $payment = $this->paymentRepository->updatePayment($payment, $paymentData);
            }

            return ['ad' => $ad, 'payment' => $payment];
        }

        $walletTransactionId = null;
        if ($costInCents > 0) {
            if ($this->paymentRepository->findByIdempotencyKey('ad-payment-'.$ad->public_id) !== null) {
                throw ValidationException::withMessages([
                    'payment_method' => [__('messages.ads.payment_method_locked')],
                ]);
            }

            $wallet = $this->walletRepository->activeForOwnerForUpdate($ownerId);
            $balanceInCents = $this->moneyToCents((string) ($wallet?->available_balance ?? '0.00'));

            if ($wallet === null || $balanceInCents < $costInCents || $wallet->currency !== $ad->currency) {
                throw ValidationException::withMessages([
                    'wallet' => [__('messages.ads.wallet_insufficient')],
                ]);
            }

            $amount = $this->centsToMoney($costInCents);
            $afterPayment = $this->centsToMoney($balanceInCents - $costInCents);
            $balances = $this->walletRepository->updateAvailableBalance($wallet, $afterPayment);
            $transaction = $this->walletTransactionRepository->createAdPayment([
                'wallet_id' => $wallet->getKey(),
                'type' => WalletTransactionTypeEnum::AdPayment,
                'status' => WalletTransactionStatusEnum::Completed,
                'amount' => $amount,
                'balance_before' => $balances['before'],
                'balance_after' => $balances['after'],
                'currency' => $wallet->currency,
                'idempotency_key' => 'ad-payment-'.$ad->public_id,
                'completed_at' => now(),
            ], $ad);
            $walletTransactionId = $transaction->getKey();
        }

        $submittedAd = $this->repository->updateForStore($ad->getKey(), $storeId, [
            'wallet_transaction_id' => $walletTransactionId,
            'status' => AdStatusEnum::PendingReview,
        ]);

        return ['ad' => $submittedAd, 'payment' => null];
    }

    public function completeOnlineAdPayment(int|string $paymentId, PaymentOperationTableEnum $operation): PaymentStatusEnum
    {
        return DB::transaction(function () use ($paymentId, $operation): PaymentStatusEnum {
            $payment = $this->paymentRepository->findForUpdateOrFail($paymentId);
            if ($payment->method !== PaymentMethodEnum::Online || $operation !== PaymentOperationTableEnum::PayTrip) {
                throw ValidationException::withMessages([
                    'payment' => [__('messages.ads.payment_unavailable')],
                ]);
            }

            if ($payment->status !== PaymentStatusEnum::Pending) {
                return $payment->status;
            }

            $metadata = $payment->metadata;
            if (! is_array($metadata)) {
                throw ValidationException::withMessages([
                    'payment' => [__('messages.ads.payment_unavailable')],
                ]);
            }

            if (($metadata['kind'] ?? null) === 'new_ad' && is_array($metadata['ad_attributes'] ?? null)) {
                $ad = $this->repository->createForStore($metadata['ad_attributes']);
            } elseif (($metadata['kind'] ?? null) === 'existing_ad') {
                $storeId = (int) ($metadata['store_id'] ?? 0);
                $adId = (int) ($metadata['ad_id'] ?? 0);
                $ad = $this->repository->findForStoreForUpdate($adId, $storeId);

                if ($ad->status === AdStatusEnum::Draft) {
                    $ad = $this->repository->updateForStore($adId, $storeId, [
                        'status' => AdStatusEnum::PendingReview,
                    ]);
                }
            } else {
                throw ValidationException::withMessages([
                    'payment' => [__('messages.ads.payment_unavailable')],
                ]);
            }

            $this->paymentRepository->updatePayment($payment, [
                'ad_id' => $ad->getKey(),
                'status' => PaymentStatusEnum::Paid,
                'paid_at' => now(),
                'metadata' => null,
            ]);

            return PaymentStatusEnum::Paid;
        });
    }

    public function failOnlineAdPayment(int|string $paymentId, PaymentOperationTableEnum $operation): PaymentStatusEnum
    {
        [$status, $mediaPath] = DB::transaction(function () use ($paymentId, $operation): array {
            $payment = $this->paymentRepository->findForUpdateOrFail($paymentId);
            if ($payment->method !== PaymentMethodEnum::Online || $operation !== PaymentOperationTableEnum::PayTrip) {
                throw ValidationException::withMessages([
                    'payment' => [__('messages.ads.payment_unavailable')],
                ]);
            }

            if ($payment->status !== PaymentStatusEnum::Pending) {
                return [$payment->status, null];
            }

            $metadata = $payment->metadata;
            if (! is_array($metadata)) {
                throw ValidationException::withMessages([
                    'payment' => [__('messages.ads.payment_unavailable')],
                ]);
            }

            $mediaPath = null;
            if (($metadata['kind'] ?? null) === 'new_ad' && is_array($metadata['ad_attributes'] ?? null)) {
                $mediaPath = $metadata['ad_attributes']['media_path'] ?? null;
            } elseif (($metadata['kind'] ?? null) === 'existing_ad') {
                $storeId = (int) ($metadata['store_id'] ?? 0);
                $adId = (int) ($metadata['ad_id'] ?? 0);
                $ad = $this->repository->findForStoreForUpdate($adId, $storeId);
                $mediaPath = $ad->media_path;
                $this->repository->deleteForStore($adId, $storeId);
            } else {
                throw ValidationException::withMessages([
                    'payment' => [__('messages.ads.payment_unavailable')],
                ]);
            }

            $this->paymentRepository->updatePayment($payment, [
                'status' => PaymentStatusEnum::Failed,
                'metadata' => null,
            ]);

            return [PaymentStatusEnum::Failed, $mediaPath];
        });

        if ($mediaPath !== null) {
            Storage::disk('public')->delete($mediaPath);
        }

        return $status;
    }

    private function creationPaymentKey(string $creationKey): string
    {
        return 'ad-creation-payment-'.$creationKey;
    }

    public function updateForStore(int $ownerId, int|string $adId, array $data): Model
    {
        $storeId = $this->assertStoreOwner($ownerId);
        $this->assertProductBelongsToStore($ownerId, $data['product_id'] ?? null);
        $ad = $this->repository->findForStore($adId, $storeId);
        $this->assertNoPendingOnlinePayment($ad);

        $updates = $data;
        if (isset($updates['ad_package_id']) || isset($updates['placement'])) {
            $packageId = (int) ($updates['ad_package_id'] ?? $ad->ad_package_id);
            $package = $this->repository->activePackage($packageId);
            if ($package === null) {
                throw ValidationException::withMessages([
                    'ad_package_id' => [__('messages.validation.store_ads.ad_package_id.exists')],
                ]);
            }
            if (isset($updates['ad_package_id'])) {
                $updates['ad_package_id'] = $package->getKey();
            }
            $placement = $updates['placement'] ?? $ad->placement->value;
            $updates['cost'] = $this->costForPlacement(AdPlacementEnum::from($placement), $package->price);
            $updates['currency'] = $package->currency;
        }
        if (isset($updates['media'])) {
            $updates['media_path'] = ImageHelper::upload($updates['media'], 'ads', null, $updates['media_type'] ?? $ad->media_type->value);
            unset($updates['media']);
        }

        return $this->repository->updateForStore($adId, $storeId, $updates);
    }

    public function toggleForStore(int $ownerId, int|string $adId): Model
    {
        $storeId = $this->assertStoreOwner($ownerId);
        $ad = $this->repository->findForStore($adId, $storeId);
        if ($ad->status === AdStatusEnum::Active || $ad->status === AdStatusEnum::Scheduled) {
            $nextStatus = AdStatusEnum::Paused;
        } elseif ($ad->status === AdStatusEnum::Paused) {
            $nextStatus = $ad->starts_at?->isFuture() ? AdStatusEnum::Scheduled : AdStatusEnum::Active;
        } else {
            throw ValidationException::withMessages([
                'status' => [__('messages.ads.toggle_unavailable')],
            ]);
        }

        return $this->repository->updateForStore($adId, $storeId, ['status' => $nextStatus]);
    }

    public function deleteForStore(int $ownerId, int|string $adId): bool
    {
        $storeId = $this->assertStoreOwner($ownerId);
        $ad = $this->repository->findForStore($adId, $storeId);
        $this->assertNoPendingOnlinePayment($ad);

        return $this->repository->deleteForStore($adId, $storeId);
    }

    private function assertNoPendingOnlinePayment(Model $ad): void
    {
        if ($this->paymentRepository->findAdPayment((int) $ad->getKey())?->status === PaymentStatusEnum::Pending) {
            throw ValidationException::withMessages([
                'payment_method' => [__('messages.ads.payment_method_locked')],
            ]);
        }
    }

    private function assertProductBelongsToStore(int $storeId, mixed $productId): void
    {
        if ($productId !== null && ! $this->repository->productBelongsToStore((int) $productId, $storeId)) {
            throw ValidationException::withMessages([
                'product_id' => [__('messages.validation.store_ads.product_id.exists')],
            ]);
        }
    }

    private function moneyToCents(string $amount): int
    {
        [$whole, $fraction] = array_pad(explode('.', $amount, 2), 2, '0');

        return ((int) $whole * 100) + (int) str_pad(substr($fraction, 0, 2), 2, '0');
    }

    private function centsToMoney(int $amount): string
    {
        return intdiv($amount, 100).'.'.str_pad((string) ($amount % 100), 2, '0', STR_PAD_LEFT);
    }

    private function costForPlacement(AdPlacementEnum $placement, string $packagePrice): string
    {
        if ($placement === AdPlacementEnum::Storefront) {
            return '0.00';
        }

        return $this->settingsRepository->adPriceForPlacement($placement) ?? $packagePrice;
    }
}
