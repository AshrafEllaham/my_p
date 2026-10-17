<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\InteractsWithCatalogModals;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\RejectWalletWithdrawalRequest;
use App\Models\Sai\WalletWithdrawalRequest;
use App\Services\Admin\WalletWithdrawalReviewService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class WalletWithdrawalController extends Controller
{
    use InteractsWithCatalogModals;

    public function __construct(
        private readonly WalletWithdrawalReviewService $service,
        private readonly DataTables $dataTables,
    ) {}

    public function index(Request $request): View|JsonResponse
    {
        if ($request->ajax()) {
            return $this->dataTables->eloquent($this->service->pendingQuery())
                ->editColumn('method', fn (WalletWithdrawalRequest $withdrawal): string => __('admin.wallet_withdrawals.methods.'.$withdrawal->method->value))
                ->editColumn('amount', fn (WalletWithdrawalRequest $withdrawal): string => $withdrawal->amount.' '.$withdrawal->currency)
                ->editColumn('created_at', fn (WalletWithdrawalRequest $withdrawal): string => $withdrawal->created_at?->format('Y-m-d H:i') ?? '')
                ->addColumn('actions', fn (WalletWithdrawalRequest $withdrawal): string => view('admin.wallet-withdrawals.parts.actions', [
                    'reviewUrl' => route('admin.wallet-withdrawals.review', $withdrawal->id),
                ])->render())
                ->rawColumns(['actions'])
                ->toJson();
        }

        return view('admin.wallet-withdrawals.index', [
            'oneObjectTitle' => __('admin.wallet_withdrawals.title'),
            'pendingCount' => $this->service->pendingCount(),
            'columns' => [
                ['data' => 'id', 'name' => 'wallet_withdrawal_requests.id', 'title' => '#'],
                ['data' => 'owner_name', 'name' => 'users.name', 'title' => __('admin.wallet_withdrawals.fields.owner')],
                ['data' => 'amount', 'name' => 'wallet_withdrawal_requests.amount', 'title' => __('admin.wallet_withdrawals.fields.amount')],
                ['data' => 'method', 'name' => 'wallet_withdrawal_requests.method', 'title' => __('admin.wallet_withdrawals.fields.method')],
                ['data' => 'destination', 'name' => 'wallet_withdrawal_requests.destination', 'title' => __('admin.wallet_withdrawals.fields.destination')],
                ['data' => 'created_at', 'name' => 'wallet_withdrawal_requests.created_at', 'title' => __('admin.wallet_withdrawals.fields.submitted_at')],
                ['data' => 'actions', 'name' => 'actions', 'title' => __('admin.catalog.fields.actions'), 'orderable' => false, 'searchable' => false],
            ],
        ]);
    }

    public function review(int $withdrawal): never
    {
        $this->modal('admin.wallet-withdrawals.parts.review', [
            'withdrawalRequest' => $this->service->findPending($withdrawal),
            'approveUrl' => route('admin.wallet-withdrawals.approve', $withdrawal),
            'rejectUrl' => route('admin.wallet-withdrawals.reject', $withdrawal),
        ]);
    }

    public function approve(int $withdrawal): never
    {
        $this->service->approve($withdrawal);
        $this->catalogSuccess(__('admin.wallet_withdrawals.messages.approved'));
    }

    public function reject(RejectWalletWithdrawalRequest $request, int $withdrawal): never
    {
        $this->service->reject($withdrawal, $request->validated('reason'));
        $this->catalogSuccess(__('admin.wallet_withdrawals.messages.rejected'));
    }
}
