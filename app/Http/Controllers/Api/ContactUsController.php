<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\ContactUsRequest;
use App\Http\Resources\Api\ContactUsResource;
use App\Services\Sai\ContactUsService;
use Illuminate\Http\JsonResponse;

class ContactUsController extends Controller
{
    public function __construct(private readonly ContactUsService $service) {}

    public function store(ContactUsRequest $request): JsonResponse
    {
        $message = $this->service->createMessage($request->validated());

        return jsonSuccess(
            ContactUsResource::make($message),
            __('messages.contact_us.created'),
        );
    }
}
