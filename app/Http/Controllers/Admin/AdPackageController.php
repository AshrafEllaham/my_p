<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\InteractsWithCatalogModals;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Catalog\AdPackageRequest;
use App\Services\Admin\Catalog\AdPackageService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class AdPackageController extends Controller
{
    use InteractsWithCatalogModals;

    public function __construct(
        private readonly AdPackageService $service,
        private readonly DataTables $dataTables,
    ) {}

    public function index(Request $request): View|JsonResponse
    {
        if ($request->ajax()) {
            return $this->dataTables->eloquent($this->service->dataTableQuery(app()->getLocale()))
                ->editColumn('is_active', fn ($package): bool => (bool) $package->is_active)
                ->addColumn('actions', fn ($package): string => view(
                    'admin.catalog.parts.actions',
                    $this->actionUrls('ad-packages', (int) $package->id),
                )->render())
                ->rawColumns(['actions'])
                ->toJson();
        }

        return view('admin.catalog.index', [
            'oneObjectTitle' => __('admin.ad_packages.title'),
            'pageDescription' => __('admin.ad_packages.description'),
            'createLabel' => __('admin.ad_packages.create'),
            'createUrl' => route('admin.ad-packages.create'),
            'dataUrl' => route('admin.ad-packages.index'),
            'columns' => [
                ['data' => 'id', 'name' => 'ad_packages.id', 'title' => '#'],
                ['data' => 'code', 'name' => 'ad_packages.code', 'title' => __('admin.ad_packages.fields.code')],
                ['data' => 'name', 'name' => 'ad_package_translation.name', 'title' => __('admin.ad_packages.fields.name')],
                ['data' => 'duration_days', 'name' => 'ad_packages.duration_days', 'title' => __('admin.ad_packages.fields.duration_days')],
                ['data' => 'price', 'name' => 'ad_packages.price', 'title' => __('admin.ad_packages.fields.price')],
                ['data' => 'currency', 'name' => 'ad_packages.currency', 'title' => __('admin.ad_packages.fields.currency')],
                ['data' => 'is_active', 'name' => 'ad_packages.is_active', 'title' => __('admin.catalog.fields.status'), 'type' => 'status'],
                ['data' => 'actions', 'name' => 'actions', 'title' => __('admin.catalog.fields.actions'), 'orderable' => false, 'searchable' => false],
            ],
        ]);
    }

    public function create(): never
    {
        $this->modal('admin.ad-packages.parts.form', [
            'package' => null,
            'action' => route('admin.ad-packages.store'),
            'method' => 'POST',
        ]);
    }

    public function store(AdPackageRequest $request): never
    {
        $this->service->create($request->validated());
        $this->catalogSuccess(__('admin.catalog.messages.created'));
    }

    public function show(int $ad_package): never
    {
        $this->modal('admin.ad-packages.parts.show', ['package' => $this->service->find($ad_package)]);
    }

    public function edit(int $ad_package): never
    {
        $this->modal('admin.ad-packages.parts.form', [
            'package' => $this->service->find($ad_package),
            'action' => route('admin.ad-packages.update', $ad_package),
            'method' => 'PUT',
        ]);
    }

    public function update(AdPackageRequest $request, int $ad_package): never
    {
        $this->service->update($ad_package, $request->validated());
        $this->catalogSuccess(__('admin.catalog.messages.updated'));
    }

    public function destroy(int $ad_package): never
    {
        $this->service->delete($ad_package);
        $this->catalogSuccess(__('admin.catalog.messages.deleted'));
    }
}
