<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\InteractsWithCatalogModals;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Catalog\MainCategoryRequest;
use App\Services\Admin\Catalog\MainCategoryService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class MainCategoryController extends Controller
{
    use InteractsWithCatalogModals;

    public function __construct(
        private readonly MainCategoryService $service,
        private readonly DataTables $dataTables,
    ) {}

    public function index(Request $request): View|JsonResponse
    {
        if ($request->ajax()) {
            return $this->dataTables->eloquent($this->service->dataTableQuery(app()->getLocale()))
                ->editColumn('is_active', fn ($category): bool => (bool) $category->is_active)
                ->addColumn('actions', fn ($category): string => view(
                    'admin.catalog.parts.actions',
                    $this->actionUrls('main-categories', (int) $category->id),
                )->render())
                ->rawColumns(['actions'])
                ->toJson();
        }

        return view('admin.catalog.index', $this->pageData());
    }

    public function create(): never
    {
        $this->modal('admin.catalog.categories.parts.form', [
            'category' => null,
            'parents' => collect(),
            'isSubCategory' => false,
            'action' => route('admin.main-categories.store'),
            'method' => 'POST',
        ]);
    }

    public function store(MainCategoryRequest $request): never
    {
        $this->service->create($request->validated());
        $this->catalogSuccess(__('admin.catalog.messages.created'));
    }

    public function show(int $main_category): never
    {
        $this->modal('admin.catalog.categories.parts.show', [
            'category' => $this->service->find($main_category),
            'isSubCategory' => false,
        ]);
    }

    public function edit(int $main_category): never
    {
        $this->modal('admin.catalog.categories.parts.form', [
            'category' => $this->service->find($main_category),
            'parents' => collect(),
            'isSubCategory' => false,
            'action' => route('admin.main-categories.update', $main_category),
            'method' => 'PUT',
        ]);
    }

    public function update(MainCategoryRequest $request, int $main_category): never
    {
        $this->service->update($main_category, $request->validated());
        $this->catalogSuccess(__('admin.catalog.messages.updated'));
    }

    public function destroy(int $main_category): never
    {
        $this->service->delete($main_category);
        $this->catalogSuccess(__('admin.catalog.messages.deleted'));
    }

    /** @return array<string, mixed> */
    private function pageData(): array
    {
        return [
            'oneObjectTitle' => __('admin.catalog.main_categories.title'),
            'pageDescription' => __('admin.catalog.main_categories.description'),
            'createLabel' => __('admin.catalog.main_categories.create'),
            'createUrl' => route('admin.main-categories.create'),
            'dataUrl' => route('admin.main-categories.index'),
            'columns' => [
                ['data' => 'id', 'name' => 'categories.id', 'title' => '#'],
                ['data' => 'name', 'name' => 'category_translation.name', 'title' => __('admin.catalog.fields.name')],
                ['data' => 'slug', 'name' => 'categories.slug', 'title' => __('admin.catalog.fields.slug')],
                ['data' => 'icon', 'name' => 'categories.icon', 'title' => __('admin.catalog.fields.icon')],
                ['data' => 'children_count', 'name' => 'children_count', 'title' => __('admin.catalog.fields.sub_categories_count'), 'searchable' => false],
                ['data' => 'sort_order', 'name' => 'categories.sort_order', 'title' => __('admin.catalog.fields.sort_order')],
                ['data' => 'is_active', 'name' => 'categories.is_active', 'title' => __('admin.catalog.fields.status'), 'type' => 'status'],
                ['data' => 'actions', 'name' => 'actions', 'title' => __('admin.catalog.fields.actions'), 'orderable' => false, 'searchable' => false],
            ],
        ];
    }
}
