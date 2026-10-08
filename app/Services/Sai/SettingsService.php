<?php

namespace App\Services\Sai;

use App\Models\Sai\Settings;
use App\Repositories\Sai\SettingsRepository;

class SettingsService
{
    public function __construct(private readonly SettingsRepository $repository) {}

    public function get(): ?Settings
    {
        return $this->repository->getSingleton();
    }
}
