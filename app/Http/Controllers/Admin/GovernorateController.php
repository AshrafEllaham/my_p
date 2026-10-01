<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\InteractsWithCatalogModals;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Catalog\GovernorateRequest;
use App\Services\Admin\Catalog\GovernorateService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class GovernorateController extends Controller
{
    use InteractsWithCatalogModals;

    public function __construct(
        private readonly GovernorateService $service,
        private readonly DataTables $dataTables,
    ) {}

    public function index(Request $request): View|JsonResponse
    {
        if ($request->ajax()) {
            return $this->dataTables->eloquent($this->service->dataTableQuery(app()->getLocale()))
                ->editColumn('is_active', fn ($governorate): bool => (bool) $governorate->is_active)
                ->addColumn('actions', fn ($governorate): string => view(
                    'admin.catalog.parts.actions',
                    $this->actionUrls('governorates', (int) $governorate->id),
                )->render())
                ->rawColumns(['actions'])
                ->toJson();
        }

        return view('admin.catalog.index', $this->pageData());
    }

    public function create(): never
    {
        $this->modal('admin.catalog.governorates.parts.form', [
            'governorate' => null,
            'countries' => $this->service->countryOptions(app()->getLocale()),
            'action' => route('admin.governorates.store'),
            'method' => 'POST',
        ]);
    }

    public function store(GovernorateRequest $request): never
    {
        $this->service->create($request->validated());
        $this->catalogSuccess(__('admin.catalog.messages.created'));
    }

    public function show(int $governorate): never
    {
        $this->modal('admin.catalog.governorates.parts.show', [
            'governorate' => $this->service->find($governorate),
        ]);
    }

    public function edit(int $governorate): never
    {
        $this->modal('admin.catalog.governorates.parts.form', [
            'governorate' => $this->service->find($governorate),
            'countries' => $this->service->countryOptions(app()->getLocale()),
            'action' => route('admin.governorates.update', $governorate),
            'method' => 'PUT',
        ]);
    }

    public function update(GovernorateRequest $request, int $governorate): never
    {
        $this->service->update($governorate, $request->validated());
        $this->catalogSuccess(__('admin.catalog.messages.updated'));
    }

    public function destroy(int $governorate): never
    {
        $this->service->delete($governorate);
        $this->catalogSuccess(__('admin.catalog.messages.deleted'));
    }

    /** @return array<string, mixed> */
    private function pageData(): array
    {
        return [
            'oneObjectTitle' => __('admin.catalog.governorates.title'),
            'pageDescription' => __('admin.catalog.governorates.description'),
            'createLabel' => __('admin.catalog.governorates.create'),
            'createUrl' => route('admin.governorates.create'),
            'dataUrl' => route('admin.governorates.index'),
            'columns' => [
                ['data' => 'id', 'name' => 'governorates.id', 'title' => '#'],
                ['data' => 'name', 'name' => 'governorate_translation.name', 'title' => __('admin.catalog.fields.name')],
                ['data' => 'country_name', 'name' => 'country_translation.name', 'title' => __('admin.catalog.fields.country')],
                ['data' => 'cities_count', 'name' => 'cities_count', 'title' => __('admin.catalog.fields.cities_count'), 'searchable' => false],
                ['data' => 'is_active', 'name' => 'governorates.is_active', 'title' => __('admin.catalog.fields.status'), 'type' => 'status'],
                ['data' => 'actions', 'name' => 'actions', 'title' => __('admin.catalog.fields.actions'), 'orderable' => false, 'searchable' => false],
            ],
        ];
    }
}
