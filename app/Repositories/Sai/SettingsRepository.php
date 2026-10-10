<?php

namespace App\Repositories\Sai;

use App\Enums\AdPlacementEnum;
use App\Models\Sai\Settings;
use App\Repositories\MainRepository;

class SettingsRepository extends MainRepository
{
    private const SINGLETON_ID = 1;

    public function __construct(Settings $model)
    {
        $this->model = $model;
    }

    public function getSingleton(): ?Settings
    {
        return $this->getModel()->newQuery()
            ->with('translations')
            ->first();
    }

    /** @param array<string, mixed> $data */
    public function saveSingleton(array $data): Settings
    {
        $settings = $this->getModel()->newQuery()->first();
        if ($settings === null) {
            $settings = $this->getModel()->newInstance();
        }

        $settings->fill($data);
        $settings->save();

        return $settings->refresh()->load('translations');
    }

    public function adPriceForPlacement(AdPlacementEnum $placement): ?string
    {
        $column = match ($placement) {
            AdPlacementEnum::Home => 'ad_home_price',
            AdPlacementEnum::Category => 'ad_category_price',
            AdPlacementEnum::Storefront => null,
        };

        if ($column === null) {
            return null;
        }

        $price = $this->getModel()->newQuery()->orderBy('id')->value($column);

        return $price === null ? null : (string) $price;
    }
}
