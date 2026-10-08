<?php

namespace App\Repositories\Sai;

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
}
