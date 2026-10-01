<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\InteractsWithCatalogModals;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Catalog\SubCategoryRequest;
use App\Services\Admin\Catalog\SubCategoryService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class SubCategoryController extends Controller
{
    use InteractsWithCatalogModals;

    public function __construct(
        private readonly SubCategoryService $service,
        private readonly DataTables $dataTables,
    ) {}

    public function index(Request $request): View|JsonResponse
    {
        if ($request->ajax()) {
            return $this->dataTables->eloquent($this->service->dataTableQuery(app()->getLocale()))
                ->editColumn('is_active', fn ($category): bool => (bool) $category->is_active)
                ->addColumn('actions', fn ($category): string => view(
                    'admin.catalog.parts.actions',
                    $this->actionUrls('sub-categories', (int) $category->id),
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
            'parents' => $this->service->mainCategoryOptions(app()->getLocale()),
            'isSubCategory' => true,
            'action' => route('admin.sub-categories.store'),
            'method' => 'POST',
        ]);
    }

    public function store(SubCategoryRequest $request): never
    {
        $this->service->create($request->validated());
        $this->catalogSuccess(__('admin.catalog.messages.created'));
    }

    public function show(int $sub_category): never
    {
        $this->modal('admin.catalog.categories.parts.show', [
            'category' => $this->service->find($sub_category),
            'isSubCategory' => true,
        ]);
    }

    public function edit(int $sub_category): never
    {
        $this->modal('admin.catalog.categories.parts.form', [
            'category' => $this->service->find($sub_category),
            'parents' => $this->service->mainCategoryOptions(app()->getLocale()),
            'isSubCategory' => true,
            'action' => route('admin.sub-categories.update', $sub_category),
            'method' => 'PUT',
        ]);
    }

    public function update(SubCategoryRequest $request, int $sub_category): never
    {
        $this->service->update($sub_category, $request->validated());
        $this->catalogSuccess(__('admin.catalog.messages.updated'));
    }

    public function destroy(int $sub_category): never
    {
        $this->service->delete($sub_category);
        $this->catalogSuccess(__('admin.catalog.messages.deleted'));
    }

    /** @return array<string, mixed> */
    private function pageData(): array
    {
        return [
            'oneObjectTitle' => __('admin.catalog.sub_categories.title'),
            'pageDescription' => __('admin.catalog.sub_categories.description'),
            'createLabel' => __('admin.catalog.sub_categories.create'),
            'createUrl' => route('admin.sub-categories.create'),
            'dataUrl' => route('admin.sub-categories.index'),
            'columns' => [
                ['data' => 'id', 'name' => 'categories.id', 'title' => '#'],
                ['data' => 'name', 'name' => 'category_translation.name', 'title' => __('admin.catalog.fields.name')],
                ['data' => 'parent_name', 'name' => 'parent_translation.name', 'title' => __('admin.catalog.fields.main_category')],
                ['data' => 'slug', 'name' => 'categories.slug', 'title' => __('admin.catalog.fields.slug')],
                ['data' => 'sort_order', 'name' => 'categories.sort_order', 'title' => __('admin.catalog.fields.sort_order')],
                ['data' => 'is_active', 'name' => 'categories.is_active', 'title' => __('admin.catalog.fields.status'), 'type' => 'status'],
                ['data' => 'actions', 'name' => 'actions', 'title' => __('admin.catalog.fields.actions'), 'orderable' => false, 'searchable' => false],
            ],
        ];
    }
}
