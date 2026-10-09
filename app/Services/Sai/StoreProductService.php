<?php

namespace App\Services\Sai;

use App\Enums\MediaTypeEnum;
use App\Enums\ProductStatusEnum;
use App\Helpers\ImageHelper;
use App\Repositories\Sai\ProductFeatureRepository;
use App\Repositories\Sai\ProductMediaRepository;
use App\Repositories\Sai\ProductRepository;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class StoreProductService
{
    public function __construct(
        private readonly ProductRepository $products,
        private readonly ProductMediaRepository $media,
        private readonly ProductFeatureRepository $features,
    ) {}

    /** @param array<string, mixed> $data */
    public function create(int $storeId, array $data): Model
    {
        $this->assertStoreOwner($storeId);

        return DB::transaction(function () use ($storeId, $data): Model {
            $published = (bool) ($data['publish'] ?? true);
            $name = $data['name'];
            $slugBase = Str::slug($name) ?: 'product';
            $slug = $slugBase.'-'.Str::lower(Str::random(8));
            $discount = $data['discount_percentage'] ?? null;
            $product = $this->products->createRecord([
                'store_id' => $storeId,
                'category_id' => $data['category_id'],
                'name' => $name,
                'description' => $data['description'],
                'slug' => $slug,
                'status' => $published ? ProductStatusEnum::Published : ProductStatusEnum::Draft,
                'price' => $data['price'],
                'original_price' => $discount ? round((float) $data['price'] / (1 - ((float) $discount / 100)), 2) : null,
                'discount_percentage' => $discount,
                'discount_ends_at' => $discount === null ? null : ($data['discount_ends_at'] ?? null),
                'stock_quantity' => $data['stock_quantity'],
                'published_at' => $published ? now() : null,
            ]);

            $featureRecords = array_map(
                fn (string $feature): array => ['product_id' => $product->getKey(), 'name' => $feature],
                $data['features'] ?? [],
            );
            $this->features->createManyRecords($featureRecords);

            $mediaRecords = [];
            foreach ($data['images'] ?? [] as $index => $image) {
                $mediaRecords[] = [
                    'product_id' => $product->getKey(),
                    'type' => MediaTypeEnum::Image,
                    'path' => ImageHelper::upload($image, 'products'),
                    'is_primary' => $index === 0,
                ];
            }
            $this->media->createManyRecords($mediaRecords);

            return $this->products->findWithMediaAndFeatures($product->getKey());
        });
    }

    /** @param array<string, mixed> $data */
    public function update(int $storeId, int|string $productId, array $data): Model
    {
        $this->assertStoreOwner($storeId);
        $newImages = $data['images'] ?? [];

        return DB::transaction(function () use ($storeId, $productId, $data, $newImages): Model {
            $current = $this->products->lockForStoreWithMediaAndFeatures($productId, $storeId);
            $existingMediaCount = $this->media->countForProduct($productId);
            if ($existingMediaCount + count($newImages) > 6) {
                throw ValidationException::withMessages([
                    'images' => [__('messages.validation.product.images.total_max')],
                ]);
            }

            $hasExistingMedia = $existingMediaCount > 0;
            $updates = array_intersect_key($data, array_flip(['name', 'category_id', 'description', 'stock_quantity']));

            if (array_key_exists('price', $data) || array_key_exists('discount_percentage', $data)) {
                $price = $data['price'] ?? $current->price;
                $discount = array_key_exists('discount_percentage', $data)
                    ? $data['discount_percentage']
                    : $current->discount_percentage;
                $updates['price'] = $price;
                $updates['discount_percentage'] = $discount;
                $updates['original_price'] = $discount === null
                    ? null
                    : round((float) $price / (1 - ((float) $discount / 100)), 2);
                $updates['discount_ends_at'] = $discount === null
                    ? null
                    : ($data['discount_ends_at'] ?? $current->discount_ends_at);
            }

            if (array_key_exists('publish', $data)) {
                $isPublished = (bool) $data['publish'];
                $updates['status'] = $isPublished ? ProductStatusEnum::Published : ProductStatusEnum::Draft;
                $updates['published_at'] = $isPublished
                    ? ($current->status === ProductStatusEnum::Published ? $current->published_at : now())
                    : null;
            }

            $product = $updates
                ? $this->products->updateForStore($productId, $storeId, $updates)
                : $current;

            if (array_key_exists('features', $data)) {
                $this->features->deleteForProduct($productId);
                $featureRecords = array_map(
                    fn (string $feature): array => ['product_id' => $productId, 'name' => $feature],
                    $data['features'],
                );
                $this->features->createManyRecords($featureRecords);
            }

            $mediaRecords = [];
            foreach ($newImages as $index => $image) {
                $mediaRecords[] = [
                    'product_id' => $productId,
                    'type' => MediaTypeEnum::Image,
                    'path' => ImageHelper::upload($image, 'products'),
                    'is_primary' => $index === 0 && ! $hasExistingMedia,
                ];
            }
            $this->media->createManyRecords($mediaRecords);

            return $this->products->findForStoreWithMediaAndFeatures($product->getKey(), $storeId);
        });
    }

    public function hide(int $storeId, int|string $productId): Model
    {
        $this->assertStoreOwner($storeId);

        return $this->products->updateForStore($productId, $storeId, [
            'status' => ProductStatusEnum::Hidden,
        ]);
    }

    public function delete(int $storeId, int|string $productId): bool
    {
        $this->assertStoreOwner($storeId);

        return $this->products->deleteForStore($productId, $storeId);
    }

    /** @param array<string, mixed> $filters */
    public function list(int $storeId, array $filters, string $locale): Builder
    {
        $this->assertStoreOwner($storeId);

        return $this->products->listForStore($storeId, $filters, $locale);
    }

    /** @return array{total_products_count: int, total_new_products_this_month_count: int, total_published_products_count: int, low_stock_products_count: int} */
    public function summary(int $storeId): array
    {
        $this->assertStoreOwner($storeId);

        return $this->products->summaryForStore($storeId);
    }

    /** @return array{product: object, analytics: array<string, mixed>} */
    public function details(int $storeId, int|string $productId, string $locale): array
    {
        $this->assertStoreOwner($storeId);

        $product = $this->products->findDetailsForStore($productId, $storeId, $locale);

        return [
            'product' => $product,
            'analytics' => $this->products->analyticsForProduct($productId),
        ];
    }

    private function assertStoreOwner(int $storeId): void
    {
        if (! $this->products->isStoreOwner($storeId)) {
            throw ValidationException::withMessages([
                'account_type' => [__('messages.profile.store_type_required')],
            ]);
        }
    }
}
