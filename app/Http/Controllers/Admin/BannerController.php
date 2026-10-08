<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AccountTypeEnum;
use App\Http\Controllers\Admin\Concerns\InteractsWithCatalogModals;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\BannerRequest;
use App\Services\Admin\BannerService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\DataTables;

class BannerController extends Controller
{
    use InteractsWithCatalogModals;

    public function __construct(
        private readonly BannerService $service,
        private readonly DataTables $dataTables,
    ) {}

    public function index(Request $request): View|JsonResponse
    {
        if ($request->ajax()) {
            $type = $request->query('type');

            return $this->dataTables->eloquent($this->service->dataTableQuery(is_string($type) ? $type : null))
                ->addColumn('image_preview', fn ($banner): string => view('admin.banners.parts.thumbnail', [
                    'url' => Storage::disk('public')->url($banner->file),
                ])->render())
                ->addColumn('type_label', fn ($banner): string => __('admin.banners.types.'.$banner->type->value))
                ->addColumn('actions', fn ($banner): string => view(
                    'admin.catalog.parts.actions',
                    $this->actionUrls('banners', (int) $banner->id),
                )->render())
                ->rawColumns(['image_preview', 'actions'])
                ->toJson();
        }

        return view('admin.catalog.index', [
            'oneObjectTitle' => __('admin.banners.title'),
            'pageDescription' => __('admin.banners.description'),
            'createLabel' => __('admin.banners.create'),
            'createUrl' => route('admin.banners.create'),
            'dataUrl' => route('admin.banners.index'),
            'columns' => [
                ['data' => 'id', 'name' => 'banners.id', 'title' => '#'],
                ['data' => 'image_preview', 'name' => 'banners.file', 'title' => __('admin.banners.fields.file'), 'orderable' => false, 'searchable' => false],
                ['data' => 'type_label', 'name' => 'banners.type', 'title' => __('admin.banners.fields.type')],
                ['data' => 'actions', 'name' => 'actions', 'title' => __('admin.catalog.fields.actions'), 'orderable' => false, 'searchable' => false],
            ],
            'filters' => [
                [
                    'name' => 'type',
                    'label' => __('admin.banners.filters.type_label'),
                    'all_label' => __('admin.banners.filters.all_types'),
                    'value' => (string) $request->query('type', ''),
                    'options' => [
                        AccountTypeEnum::User->value => __('admin.banners.types.user'),
                        AccountTypeEnum::Store->value => __('admin.banners.types.store'),
                    ],
                ],
            ],
        ]);
    }

    public function create(): never
    {
        $this->modal('admin.banners.parts.form', [
            'banner' => null,
            'action' => route('admin.banners.store'),
            'method' => 'POST',
        ]);
    }

    public function store(BannerRequest $request): never
    {
        $this->service->create($request->validated());
        $this->catalogSuccess(__('admin.catalog.messages.created'));
    }

    public function show(int $banner): never
    {
        $this->modal('admin.banners.parts.show', [
            'banner' => $this->service->find($banner),
        ]);
    }

    public function edit(int $banner): never
    {
        $this->modal('admin.banners.parts.form', [
            'banner' => $this->service->find($banner),
            'action' => route('admin.banners.update', $banner),
            'method' => 'PUT',
        ]);
    }

    public function update(BannerRequest $request, int $banner): never
    {
        $this->service->update($banner, $request->validated());
        $this->catalogSuccess(__('admin.catalog.messages.updated'));
    }

    public function destroy(int $banner): never
    {
        $this->service->delete($banner);
        $this->catalogSuccess(__('admin.catalog.messages.deleted'));
    }
}
