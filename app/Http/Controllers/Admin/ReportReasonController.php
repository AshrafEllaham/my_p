<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\InteractsWithCatalogModals;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Catalog\ReportReasonRequest;
use App\Services\Admin\Catalog\ReportReasonService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class ReportReasonController extends Controller
{
    use InteractsWithCatalogModals;

    public function __construct(
        private readonly ReportReasonService $service,
        private readonly DataTables $dataTables,
    ) {}

    public function index(Request $request): View|JsonResponse
    {
        if ($request->ajax()) {
            return $this->dataTables->eloquent($this->service->dataTableQuery(app()->getLocale()))
                ->editColumn('type', fn ($reason): string => __('admin.report_reasons.types.'.$reason->type->value))
                ->addColumn('actions', fn ($reason): string => view(
                    'admin.catalog.parts.actions',
                    $this->actionUrls('report-reasons', (int) $reason->id),
                )->render())
                ->rawColumns(['actions'])
                ->toJson();
        }

        return view('admin.catalog.index', [
            'oneObjectTitle' => __('admin.report_reasons.title'),
            'pageDescription' => __('admin.report_reasons.description'),
            'createLabel' => __('admin.report_reasons.create'),
            'createUrl' => route('admin.report-reasons.create'),
            'dataUrl' => route('admin.report-reasons.index'),
            'columns' => [
                ['data' => 'id', 'name' => 'report_reasons.id', 'title' => '#'],
                ['data' => 'type', 'name' => 'report_reasons.type', 'title' => __('admin.report_reasons.fields.type')],
                ['data' => 'title', 'name' => 'reason_translation.title', 'title' => __('admin.report_reasons.fields.title')],
                ['data' => 'actions', 'name' => 'actions', 'title' => __('admin.catalog.fields.actions'), 'orderable' => false, 'searchable' => false],
            ],
        ]);
    }

    public function create(): never
    {
        $this->modal('admin.report-reasons.parts.form', [
            'reason' => null,
            'action' => route('admin.report-reasons.store'),
            'method' => 'POST',
        ]);
    }

    public function store(ReportReasonRequest $request): never
    {
        $this->service->create($request->validated());
        $this->catalogSuccess(__('admin.catalog.messages.created'));
    }

    public function show(int $report_reason): never
    {
        $this->modal('admin.report-reasons.parts.show', ['reason' => $this->service->find($report_reason)]);
    }

    public function edit(int $report_reason): never
    {
        $this->modal('admin.report-reasons.parts.form', [
            'reason' => $this->service->find($report_reason),
            'action' => route('admin.report-reasons.update', $report_reason),
            'method' => 'PUT',
        ]);
    }

    public function update(ReportReasonRequest $request, int $report_reason): never
    {
        $this->service->update($report_reason, $request->validated());
        $this->catalogSuccess(__('admin.catalog.messages.updated'));
    }

    public function destroy(int $report_reason): never
    {
        $this->service->delete($report_reason);
        $this->catalogSuccess(__('admin.catalog.messages.deleted'));
    }
}
