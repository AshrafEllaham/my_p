<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\InteractsWithCatalogModals;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\RejectAdRequest;
use App\Models\Sai\Ad;
use App\Services\Admin\AdReviewService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class AdReviewController extends Controller
{
    use InteractsWithCatalogModals;

    public function __construct(
        private readonly AdReviewService $service,
        private readonly DataTables $dataTables,
    ) {}

    public function index(Request $request): View|JsonResponse
    {
        if ($request->ajax()) {
            return $this->dataTables->eloquent($this->service->pendingQuery())
                ->editColumn('placement', fn (Ad $ad): string => __('admin.ads.placements.'.$ad->placement->value))
                ->editColumn('cost', fn (Ad $ad): string => $ad->cost.' '.$ad->currency)
                ->editColumn('created_at', fn (Ad $ad): string => $ad->created_at?->format('Y-m-d H:i') ?? '')
                ->addColumn('actions', fn (Ad $ad): string => view('admin.ads.parts.actions', [
                    'reviewUrl' => route('admin.ads.review', $ad->id),
                ])->render())
                ->rawColumns(['actions'])
                ->toJson();
        }

        return view('admin.ads.index', [
            'oneObjectTitle' => __('admin.ads.title'),
            'pendingCount' => $this->service->pendingCount(),
            'columns' => [
                ['data' => 'id', 'name' => 'ads.id', 'title' => '#'],
                ['data' => 'title', 'name' => 'ads.title', 'title' => __('admin.ads.fields.title')],
                ['data' => 'store_owner_name', 'name' => 'users.name', 'title' => __('admin.ads.fields.store')],
                ['data' => 'placement', 'name' => 'ads.placement', 'title' => __('admin.ads.fields.placement')],
                ['data' => 'cost', 'name' => 'ads.cost', 'title' => __('admin.ads.fields.cost')],
                ['data' => 'created_at', 'name' => 'ads.created_at', 'title' => __('admin.ads.fields.submitted_at')],
                ['data' => 'actions', 'name' => 'actions', 'title' => __('admin.catalog.fields.actions'), 'orderable' => false, 'searchable' => false],
            ],
        ]);
    }

    public function review(int $ad): never
    {
        $this->modal('admin.ads.parts.review', [
            'ad' => $this->service->findPending($ad),
            'approveUrl' => route('admin.ads.approve', $ad),
            'rejectUrl' => route('admin.ads.reject', $ad),
        ]);
    }

    public function approve(int $ad): never
    {
        $this->service->approve($ad);
        $this->catalogSuccess(__('admin.ads.messages.approved'));
    }

    public function reject(RejectAdRequest $request, int $ad): never
    {
        $this->service->reject($ad, $request->validated('reason'));
        $this->catalogSuccess(__('admin.ads.messages.rejected'));
    }
}
