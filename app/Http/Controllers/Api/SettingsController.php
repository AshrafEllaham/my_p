<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\GetSettingsRequest;
use App\Http\Resources\Api\SettingsResource;
use App\Services\Sai\SettingsService;
use Illuminate\Http\JsonResponse;

class SettingsController extends Controller
{
    public function __construct(private readonly SettingsService $service) {}

    public function show(GetSettingsRequest $request): JsonResponse
    {
        $settings = $this->service->get();

        return jsonSuccess(
            $settings === null ? null : SettingsResource::make($settings),
            __('messages.settings.loaded'),
        );
    }
}
