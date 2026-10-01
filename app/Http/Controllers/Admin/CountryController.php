<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\InteractsWithCatalogModals;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Catalog\CountryRequest;
use App\Services\Admin\Catalog\CountryService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class CountryController extends Controller
{
    use InteractsWithCatalogModals;

    public function __construct(
        private readonly CountryService $service,
        private readonly DataTables $dataTables,
    ) {}

    public function index(Request $request): View|JsonResponse
    {
        if ($request->ajax()) {
            return $this->dataTables->eloquent($this->service->dataTableQuery(app()->getLocale()))
                ->editColumn('is_active', fn($country): bool => (bool) $country->is_active)
                ->addColumn('actions', fn($country): string => view(
                    'admin.catalog.parts.actions',
                    $this->actionUrls('countries', (int) $country->id),
                )->render())
                ->rawColumns(['actions'])
                ->toJson();
        }

        return view('admin.catalog.index', $this->pageData());
    }

    public function create(): never
    {
        $this->modal('admin.catalog.countries.parts.form', [
            'country' => null,
            'action' => route('admin.countries.store'),
            'method' => 'POST',
        ]);
    }

    public function store(CountryRequest $request): never
    {
        $this->service->create($request->validated());
        $this->catalogSuccess(__('admin.catalog.messages.created'));
    }

    public function show(int $country): never
    {
        $this->modal('admin.catalog.countries.parts.show', [
            'country' => $this->service->find($country),
        ]);
    }

    public function edit(int $country): never
    {
        $this->modal('admin.catalog.countries.parts.form', [
            'country' => $this->service->find($country),
            'action' => route('admin.countries.update', $country),
            'method' => 'PUT',
        ]);
    }

    public function update(CountryRequest $request, int $country): never
    {
        $this->service->update($country, $request->validated());
        $this->catalogSuccess(__('admin.catalog.messages.updated'));
    }

    public function destroy(int $country): never
    {
        $this->service->delete($country);
        $this->catalogSuccess(__('admin.catalog.messages.deleted'));
    }

    /** @return array<string, mixed> */
    private function pageData(): array
    {
        return [
            'oneObjectTitle' => __('admin.catalog.countries.title'),
            'pageDescription' => __('admin.catalog.countries.description'),
            'createLabel' => __('admin.catalog.countries.create'),
            'createUrl' => route('admin.countries.create'),
            'dataUrl' => route('admin.countries.index'),
            'columns' => [
                ['data' => 'id', 'name' => 'countries.id', 'title' => '#'],
                ['data' => 'flag', 'name' => 'countries.flag', 'title' => __('admin.catalog.fields.flag')],
                ['data' => 'name', 'name' => 'country_translation.name', 'title' => __('admin.catalog.fields.name')],
                ['data' => 'code', 'name' => 'countries.code', 'title' => __('admin.catalog.fields.code')],
                ['data' => 'phone_code', 'name' => 'countries.phone_code', 'title' => __('admin.catalog.fields.phone_code')],
                ['data' => 'governorates_count', 'name' => 'governorates_count', 'title' => __('admin.catalog.fields.governorates_count'), 'searchable' => false],
                ['data' => 'is_active', 'name' => 'countries.is_active', 'title' => __('admin.catalog.fields.status'), 'type' => 'status'],
                ['data' => 'actions', 'name' => 'actions', 'title' => __('admin.catalog.fields.actions'), 'orderable' => false, 'searchable' => false],
            ],
        ];
    }
}
