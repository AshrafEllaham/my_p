<?php

namespace App\Http\Controllers\Api\User;

use App\Enums\ReturnResolutionTypeEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\User\CancelUserOrderRequest;
use App\Http\Requests\Api\User\CreateOrderDisputeRequest;
use App\Http\Requests\Api\User\CreateOrderReviewRequest;
use App\Http\Requests\Api\User\CreateUserOrderRequest;
use App\Http\Requests\Api\User\ListUserOrdersRequest;
use App\Http\Requests\Api\User\ReplyOrderDisputeRequest;
use App\Http\Requests\Api\User\SubmitOrderReturnRequest;
use App\Http\Resources\Api\User\OrderDisputeResource;
use App\Http\Resources\Api\User\OrderReceiptResource;
use App\Http\Resources\Api\User\OrderResource;
use App\Http\Resources\Api\User\ReturnRequestResource;
use App\Http\Resources\Api\User\UserProductReviewResource;
use App\Services\Sai\OrderAfterSalesService;
use App\Services\Sai\OrderCheckoutService;
use App\Services\Sai\UserOrderLifecycleService;
use App\Services\Sai\UserOrderReadService;
use Illuminate\Http\JsonResponse;

class UserOrderController extends Controller
{
    public function __construct(
        private readonly UserOrderReadService $orders,
        private readonly OrderCheckoutService $checkout,
        private readonly OrderAfterSalesService $afterSales,
        private readonly UserOrderLifecycleService $lifecycle,
    ) {}

    public function index(ListUserOrdersRequest $request): JsonResponse
    {
        return generalReturn(
            $request,
            $this->orders->list((int) auth('api')->id(), $request->validated()),
            OrderResource::class,
            __('messages.orders.listed'),
        );
    }

    public function store(CreateUserOrderRequest $request): JsonResponse
    {
        $order = $this->checkout->create((int) auth('api')->id(), $request->validated());

        return jsonSuccess(OrderResource::make($order), __('messages.orders.created'));
    }

    public function show(int $order): JsonResponse
    {
        return jsonSuccess(
            OrderResource::make($this->orders->find((int) auth('api')->id(), $order)),
            __('messages.orders.retrieved'),
        );
    }

    public function receipt(int $order): JsonResponse
    {
        return jsonSuccess(
            OrderReceiptResource::make($this->orders->receipt((int) auth('api')->id(), $order)),
            __('messages.orders.receipt_retrieved'),
        );
    }

    public function cancel(CancelUserOrderRequest $request, int $order): JsonResponse
    {
        $updatedOrder = $this->afterSales->cancel(
            (int) auth('api')->id(),
            $order,
            $request->validated()['reason'] ?? null,
        );

        return jsonSuccess(OrderResource::make($updatedOrder), __('messages.orders.cancelled'));
    }

    public function review(CreateOrderReviewRequest $request, int $order): JsonResponse
    {
        $review = $this->afterSales->review((int) auth('api')->id(), $order, $request->validated());

        return jsonSuccess(UserProductReviewResource::make($review), __('messages.orders.review_created'));
    }

    public function requestReturn(SubmitOrderReturnRequest $request, int $order): JsonResponse
    {
        $returnRequest = $this->afterSales->requestReturn(
            (int) auth('api')->id(),
            $order,
            $request->validated(),
            ReturnResolutionTypeEnum::Refund,
        );

        return jsonSuccess(ReturnRequestResource::make($returnRequest), __('messages.orders.return_requested'));
    }

    public function requestExchange(SubmitOrderReturnRequest $request, int $order): JsonResponse
    {
        $returnRequest = $this->afterSales->requestReturn(
            (int) auth('api')->id(),
            $order,
            $request->validated(),
            ReturnResolutionTypeEnum::Exchange,
        );

        return jsonSuccess(ReturnRequestResource::make($returnRequest), __('messages.orders.exchange_requested'));
    }

    public function confirmCompletion(int $order): JsonResponse
    {
        $updatedOrder = $this->lifecycle->confirmCompletion((int) auth('api')->id(), $order);

        return jsonSuccess(OrderResource::make($updatedOrder), __('messages.orders.customer_completion_confirmed'));
    }

    public function openDispute(CreateOrderDisputeRequest $request, int $order): JsonResponse
    {
        $dispute = $this->lifecycle->openDispute((int) auth('api')->id(), $order, $request->validated());

        return jsonSuccess(OrderDisputeResource::make($dispute), __('messages.orders.dispute_opened'));
    }

    public function dispute(int $order): JsonResponse
    {
        return jsonSuccess(
            OrderDisputeResource::make($this->lifecycle->dispute((int) auth('api')->id(), $order)),
            __('messages.orders.dispute_retrieved'),
        );
    }

    public function replyToDispute(ReplyOrderDisputeRequest $request, int $order): JsonResponse
    {
        $dispute = $this->lifecycle->replyToDispute((int) auth('api')->id(), $order, $request->validated());

        return jsonSuccess(OrderDisputeResource::make($dispute), __('messages.orders.dispute_reply_sent'));
    }
}
