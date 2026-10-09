<?php

namespace App\Services\Sai;

use App\Enums\AdStatusEnum;
use App\Helpers\ImageHelper;
use App\Models\Sai\AdPackage;
use App\Repositories\Sai\AdRepository;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AdService
{
    public function __construct(private readonly AdRepository $repository) {}

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

    public function detailsForStore(int $ownerId, int|string $adId): Model
    {
        $storeId = $this->assertStoreOwner($ownerId);

        return $this->repository->findDetailsForStore($adId, $storeId);
    }

    /** @return Collection<int, AdPackage> */
    public function activePackagesForStore(int $ownerId): Collection
    {
        $this->assertStoreOwner($ownerId);

        return $this->repository->activePackages();
    }

    public function createForStore(int $ownerId, array $data): Model
    {
        $storeId = $this->assertStoreOwner($ownerId);
        $this->assertProductBelongsToStore($ownerId, $data['product_id'] ?? null);
        $package = $this->repository->activePackage((int) $data['ad_package_id']);
        if ($package === null) {
            throw ValidationException::withMessages([
                'ad_package_id' => [__('messages.validation.store_ads.ad_package_id.exists')],
            ]);
        }
        $media = $data['media'];
        unset($data['media'], $data['ad_package_id']);

        return $this->repository->createForStore($data + [
            'public_id' => (string) Str::uuid(),
            'store_id' => $storeId,
            'ad_package_id' => $package->getKey(),
            'media_path' => ImageHelper::upload($media, 'ads', null, $data['media_type']),
            'status' => AdStatusEnum::Draft,
            'cost' => $package->price,
            'currency' => $package->currency,
        ]);
    }

    public function updateForStore(int $ownerId, int|string $adId, array $data): Model
    {
        $storeId = $this->assertStoreOwner($ownerId);
        $this->assertProductBelongsToStore($ownerId, $data['product_id'] ?? null);
        $ad = $this->repository->findForStore($adId, $storeId);
        $updates = $data;
        if (isset($updates['ad_package_id'])) {
            $package = $this->repository->activePackage((int) $updates['ad_package_id']);
            if ($package === null) {
                throw ValidationException::withMessages([
                    'ad_package_id' => [__('messages.validation.store_ads.ad_package_id.exists')],
                ]);
            }
            $updates['ad_package_id'] = $package->getKey();
            $updates['cost'] = $package->price;
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

        return $this->repository->deleteForStore($adId, $storeId);
    }

    private function assertProductBelongsToStore(int $storeId, mixed $productId): void
    {
        if ($productId !== null && ! $this->repository->productBelongsToStore((int) $productId, $storeId)) {
            throw ValidationException::withMessages([
                'product_id' => [__('messages.validation.store_ads.product_id.exists')],
            ]);
        }
    }
}
