<?php

namespace App\Http\Controllers\Api\Store;

use App\Enums\AdStatusEnum;
use App\Enums\PaymentMethodEnum;
use App\Enums\PaymentOperationTableEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Store\ListStoreAdsRequest;
use App\Http\Requests\Api\Store\SaveStoreAdRequest;
use App\Http\Requests\Api\Store\SubmitStoreAdRequest;
use App\Http\Resources\Api\Store\StoreAdDetailsResource;
use App\Http\Resources\Api\Store\StoreAdPackageResource;
use App\Http\Resources\Api\Store\StoreAdResource;
use App\Services\Sai\AdService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;

class StoreAdController extends Controller
{
    public function __construct(private readonly AdService $service) {}

    public function index(ListStoreAdsRequest $request): JsonResponse
    {
        $storeId = (int) auth('api')->id();

        return generalReturn(
            $request,
            $this->service->listForStore($storeId, $request->validated()),
            StoreAdResource::class,
            __('messages.ads.listed'),
            $this->service->summary($storeId),
        );
    }

    public function show(int $ad): JsonResponse
    {
        $details = $this->service->detailsForStore((int) auth('api')->id(), $ad);

        return jsonSuccess(StoreAdDetailsResource::make($details), __('messages.ads.retrieved'));
    }

    public function packages(): JsonResponse
    {
        $packages = $this->service->activePackagesForStore();

        return jsonSuccess(StoreAdPackageResource::collection($packages), __('messages.ads.packages_listed'));
    }

    public function walletBalance(): JsonResponse
    {
        return jsonSuccess(
            $this->service->walletBalanceForStore((int) auth('api')->id()),
            __('messages.ads.wallet_balance_loaded'),
        );
    }

    public function store(SaveStoreAdRequest $request): JsonResponse
    {
        $result = $this->service->createForStore((int) auth('api')->id(), $request->validated());
        $ad = $result['ad'];
        $message = $result['payment'] !== null
            ? __('messages.ads.payment_required')
            : ($ad->status === AdStatusEnum::PendingReview
                ? __('messages.ads.submitted')
                : __('messages.ads.created'));

        return $this->submissionResponse($ad, $result['payment'], $message);
    }

    public function submit(SubmitStoreAdRequest $request, int $ad): JsonResponse
    {
        $result = $this->service->submitForReview(
            (int) auth('api')->id(),
            $ad,
            PaymentMethodEnum::from($request->validated('payment_method')),
        );

        $message = $result['payment'] !== null
            ? __('messages.ads.payment_required')
            : __('messages.ads.submitted');

        return $this->submissionResponse($result['ad'], $result['payment'], $message);
    }

    public function update(SaveStoreAdRequest $request, int $ad): JsonResponse
    {
        $updatedAd = $this->service->updateForStore((int) auth('api')->id(), $ad, $request->validated());

        return jsonSuccess(StoreAdResource::make($updatedAd), __('messages.ads.updated'));
    }

    public function toggle(int $ad): JsonResponse
    {
        $updatedAd = $this->service->toggleForStore((int) auth('api')->id(), $ad);

        return jsonSuccess(StoreAdResource::make($updatedAd), __('messages.ads.toggled'));
    }

    public function destroy(int $ad): JsonResponse
    {
        $this->service->deleteForStore((int) auth('api')->id(), $ad);

        return jsonSuccess(null, __('messages.ads.deleted'));
    }

    private function submissionResponse(?Model $ad, ?Model $payment, string $message): JsonResponse
    {
        if ($payment !== null) {
            $operation = PaymentOperationTableEnum::PayTrip->value;

            return jsonSuccess([
                'redirectUrl' => route('web.pay_online', ['id' => $payment->getKey(), 'type' => $operation]),
                'paymentSuccess' => route('payment.success', ['id' => $payment->getKey(), 'type' => $operation]),
                'paymentaFiled' => route('payment.failed', ['id' => $payment->getKey(), 'type' => $operation]),
            ], $message);
        }

        return jsonSuccess($ad === null ? null : StoreAdResource::make($ad), $message);
    }
}
