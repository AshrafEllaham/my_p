<?php

namespace App\Http\Controllers\Api\User;

use App\Enums\UserReportTypeEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\User\ListReportReasonsRequest;
use App\Http\Requests\Api\User\SubmitProductReportRequest;
use App\Http\Requests\Api\User\SubmitStoreReportRequest;
use App\Http\Resources\Api\User\ReportReasonResource;
use App\Http\Resources\Api\User\UserReportResource;
use App\Services\Sai\UserReportService;
use Illuminate\Http\JsonResponse;

class UserReportController extends Controller
{
    public function __construct(private readonly UserReportService $service) {}

    public function reasons(ListReportReasonsRequest $request): JsonResponse
    {
        $type = UserReportTypeEnum::from($request->validated('type'));

        return jsonSuccess(
            ReportReasonResource::collection($this->service->reasons(app()->getLocale(), $type)),
            __('messages.user_reports.reasons_listed'),
        );
    }

    public function storeProduct(SubmitProductReportRequest $request, int $product): JsonResponse
    {
        $data = $request->validated();
        $reasonId = (int) $data['report_reason_id'];
        $reportId = $this->service->reportProduct($product, (int) auth('api')->id(), $reasonId);

        return jsonSuccess(UserReportResource::make([
            'id' => $reportId,
            'type' => UserReportTypeEnum::Product->value,
            'item_id' => $product,
            'report_reason_id' => $reasonId,
        ]), __('messages.user_reports.submitted'));
    }

    public function storeStoreReport(SubmitStoreReportRequest $request, int $store): JsonResponse
    {
        $data = $request->validated();
        $reasonId = (int) $data['report_reason_id'];
        $reportId = $this->service->reportStore($store, (int) auth('api')->id(), $reasonId);

        return jsonSuccess(UserReportResource::make([
            'id' => $reportId,
            'type' => UserReportTypeEnum::Store->value,
            'item_id' => $store,
            'report_reason_id' => $reasonId,
        ]), __('messages.user_reports.submitted'));
    }
}
