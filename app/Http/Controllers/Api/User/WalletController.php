<?php

namespace App\Http\Controllers\Api\User;

use App\Enums\PaymentOperationTableEnum;
use App\Enums\WalletTransactionFilterEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Wallet\ListWalletTransactionsRequest;
use App\Http\Requests\Api\Wallet\RequestWalletWithdrawalRequest;
use App\Http\Requests\Api\Wallet\TopUpWalletRequest;
use App\Http\Requests\Api\Wallet\TransferWalletBalanceRequest;
use App\Http\Resources\Api\Wallet\WalletTransactionResource;
use App\Http\Resources\Api\Wallet\WalletTransferResource;
use App\Http\Resources\Api\Wallet\WalletWithdrawalRequestResource;
use App\Services\Sai\WalletTopUpService;
use App\Services\Sai\WalletTransactionService;
use App\Services\Sai\WalletTransferService;
use App\Services\Sai\WalletWithdrawalRequestService;
use Illuminate\Http\JsonResponse;

class WalletController extends Controller
{
    public function __construct(
        private readonly WalletTopUpService $service,
        private readonly WalletTransactionService $transactionsService,
        private readonly WalletWithdrawalRequestService $withdrawalRequestsService,
        private readonly WalletTransferService $transfersService,
    ) {}

    public function index(ListWalletTransactionsRequest $request): JsonResponse
    {
        $ownerId = (int) auth('api')->id();
        $wallet = $this->service->balanceForOwner($ownerId);
        $wallet['has_pending_withdrawal'] = $this->withdrawalRequestsService->hasPendingForOwner($ownerId);

        return generalReturn(
            $request,
            $this->transactionsService->listForOwner(
                $ownerId,
                $request->enum('filter', WalletTransactionFilterEnum::class),
            ),
            WalletTransactionResource::class,
            __('messages.wallet.transactions_listed'),
            [
                'wallet' => [
                    'balance' => $wallet['balance'],
                    'pending_balance' => $wallet['pending_balance'],
                    'currency' => $wallet['currency'],
                    'has_pending_withdrawal' => $wallet['has_pending_withdrawal'],
                ],
            ]
        );
    }

    public function store(TopUpWalletRequest $request): JsonResponse
    {
        $payment = $this->service->requestTopUp((int) auth('api')->id(), $request->validated());
        $operation = PaymentOperationTableEnum::WalletTopUp->value;

        return jsonSuccess([
            'payment_id' => $payment['id'],
            'status' => $payment['status']->value,
            'amount' => $payment['amount'],
            'currency' => $payment['currency'],
            'redirectUrl' => route('web.pay_online', ['id' => $payment['id'], 'type' => $operation]),
            'paymentSuccess' => route('payment.success', ['id' => $payment['id'], 'type' => $operation]),
            'paymentFailed' => route('payment.failed', ['id' => $payment['id'], 'type' => $operation]),
        ], __('messages.wallet.payment_required'));
    }

    public function requestWithdrawal(RequestWalletWithdrawalRequest $request): JsonResponse
    {
        $withdrawalRequest = $this->withdrawalRequestsService->requestWithdrawal(
            (int) auth('api')->id(),
            $request->validated(),
        );

        return jsonSuccess(
            WalletWithdrawalRequestResource::make($withdrawalRequest),
            __('messages.wallet.withdrawal_request_created'),
        );
    }

    public function transfer(TransferWalletBalanceRequest $request): JsonResponse
    {
        $transfer = $this->transfersService->transfer(
            (int) auth('api')->id(),
            $request->validated(),
        );

        return jsonSuccess(
            WalletTransferResource::make($transfer),
            __('messages.wallet.transfer_created'),
        );
    }
}
