<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\InteractsWithCatalogModals;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Catalog\CityRequest;
use App\Services\Admin\Catalog\CityService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class CityController extends Controller
{
    use InteractsWithCatalogModals;

    public function __construct(
        private readonly CityService $service,
        private readonly DataTables $dataTables,
    ) {}

    public function index(Request $request): View|JsonResponse
    {
        if ($request->ajax()) {
            return $this->dataTables->eloquent($this->service->dataTableQuery(app()->getLocale()))
                ->editColumn('is_active', fn ($city): bool => (bool) $city->is_active)
                ->addColumn('actions', fn ($city): string => view(
                    'admin.catalog.parts.actions',
                    $this->actionUrls('cities', (int) $city->id),
                )->render())
                ->rawColumns(['actions'])
                ->toJson();
        }

        return view('admin.catalog.index', $this->pageData());
    }

    public function create(): never
    {
        $this->modal('admin.catalog.cities.parts.form', [
            'city' => null,
            'governorates' => $this->service->governorateOptions(app()->getLocale()),
            'action' => route('admin.cities.store'),
            'method' => 'POST',
        ]);
    }

    public function store(CityRequest $request): never
    {
        $this->service->create($request->validated());
        $this->catalogSuccess(__('admin.catalog.messages.created'));
    }

    public function show(int $city): never
    {
        $this->modal('admin.catalog.cities.parts.show', [
            'city' => $this->service->find($city),
        ]);
    }

    public function edit(int $city): never
    {
        $this->modal('admin.catalog.cities.parts.form', [
            'city' => $this->service->find($city),
            'governorates' => $this->service->governorateOptions(app()->getLocale()),
            'action' => route('admin.cities.update', $city),
            'method' => 'PUT',
        ]);
    }

    public function update(CityRequest $request, int $city): never
    {
        $this->service->update($city, $request->validated());
        $this->catalogSuccess(__('admin.catalog.messages.updated'));
    }

    public function destroy(int $city): never
    {
        $this->service->delete($city);
        $this->catalogSuccess(__('admin.catalog.messages.deleted'));
    }

    /** @return array<string, mixed> */
    private function pageData(): array
    {
        return [
            'oneObjectTitle' => __('admin.catalog.cities.title'),
            'pageDescription' => __('admin.catalog.cities.description'),
            'createLabel' => __('admin.catalog.cities.create'),
            'createUrl' => route('admin.cities.create'),
            'dataUrl' => route('admin.cities.index'),
            'columns' => [
                ['data' => 'id', 'name' => 'cities.id', 'title' => '#'],
                ['data' => 'name', 'name' => 'city_translation.name', 'title' => __('admin.catalog.fields.name')],
                ['data' => 'governorate_name', 'name' => 'governorate_translation.name', 'title' => __('admin.catalog.fields.governorate')],
                ['data' => 'country_name', 'name' => 'country_translation.name', 'title' => __('admin.catalog.fields.country')],
                ['data' => 'is_active', 'name' => 'cities.is_active', 'title' => __('admin.catalog.fields.status'), 'type' => 'status'],
                ['data' => 'actions', 'name' => 'actions', 'title' => __('admin.catalog.fields.actions'), 'orderable' => false, 'searchable' => false],
            ],
        ];
    }
}
