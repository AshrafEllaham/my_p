<?php

namespace App\Http\Controllers\Api\Store;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Store\DrawStoreCompetitionRequest;
use App\Http\Requests\Api\Store\ListStoreCompetitionsRequest;
use App\Http\Requests\Api\Store\SaveStoreCompetitionRequest;
use App\Http\Resources\Api\Store\StoreCompetitionDetailsResource;
use App\Http\Resources\Api\Store\StoreCompetitionDrawResource;
use App\Http\Resources\Api\Store\StoreCompetitionResource;
use App\Services\Sai\CompetitionService;
use Illuminate\Http\JsonResponse;

class StoreCompetitionController extends Controller
{
    public function __construct(private readonly CompetitionService $service) {}

    public function index(ListStoreCompetitionsRequest $request): JsonResponse
    {
        $result = $this->service->listForStore((int) auth('api')->id(), $request->validated());

        return generalReturn(
            $request,
            $result->query,
            StoreCompetitionResource::class,
            __('messages.competitions.listed'),
            ['summary' => $result->summary],
        );
    }

    public function show(int $competition): JsonResponse
    {
        $record = $this->service->detailsForStore((int) auth('api')->id(), $competition);

        return jsonSuccess(StoreCompetitionDetailsResource::make($record), __('messages.competitions.retrieved'));
    }

    public function store(SaveStoreCompetitionRequest $request): JsonResponse
    {
        $record = $this->service->createForStore((int) auth('api')->id(), $request->validated());

        return jsonSuccess(StoreCompetitionResource::make($record), __('messages.competitions.created'));
    }

    public function update(SaveStoreCompetitionRequest $request, int $competition): JsonResponse
    {
        $record = $this->service->updateForStore((int) auth('api')->id(), $competition, $request->validated());

        return jsonSuccess(StoreCompetitionResource::make($record), __('messages.competitions.updated'));
    }

    public function draw(DrawStoreCompetitionRequest $request, int $competition): JsonResponse
    {
        $result = $this->service->drawForStore(
            (int) auth('api')->id(),
            $competition,
            $request->validated()['correct_answer'],
        );

        return jsonSuccess(StoreCompetitionDrawResource::make($result), __('messages.competitions.drawn'));
    }

    public function destroy(int $competition): JsonResponse
    {
        $this->service->deleteForStore((int) auth('api')->id(), $competition);

        return jsonSuccess(null, __('messages.competitions.deleted'));
    }
}
