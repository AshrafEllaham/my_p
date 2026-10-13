<?php

namespace App\Http\Controllers\Api\User;

use App\Enums\FavoriteTypeEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\User\ToggleFavoriteRequest;
use App\Services\Sai\FavoriteService;
use Illuminate\Http\JsonResponse;

class UserFavoriteController extends Controller
{
    public function __construct(private readonly FavoriteService $service) {}

    public function toggle(ToggleFavoriteRequest $request): JsonResponse
    {
        $data = $request->validated();
        $type = FavoriteTypeEnum::from($data['type']);
        $isFavorite = $this->service->toggle(
            (int) auth('api')->id(),
            $type,
            (int) $data['favoriteable_id'],
        );

        return jsonSuccess(
            [
                'type' => $type->value,
                'favoriteable_id' => (int) $data['favoriteable_id'],
                'is_favorite' => $isFavorite,
            ],
            __('messages.user_favorites.'.($isFavorite ? 'added' : 'removed')),
        );
    }
}
