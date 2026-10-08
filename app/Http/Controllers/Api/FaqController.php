<?php

namespace App\Http\Controllers\Api;

use App\Enums\AccountTypeEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\ListFaqsRequest;
use App\Http\Resources\Api\FaqResource;
use App\Services\Sai\FaqService;
use Illuminate\Http\JsonResponse;

class FaqController extends Controller
{
    public function __construct(private readonly FaqService $service) {}

    public function index(ListFaqsRequest $request): JsonResponse
    {
        $data = $request->validated();
        $type = isset($data['type'])
            ? AccountTypeEnum::from($data['type'])
            : null;

      return generalReturn($request, $this->service->listQuery($type), FaqResource::class, __('messages.faqs.listed'));
    }
}
