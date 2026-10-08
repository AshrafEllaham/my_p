<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AccountTypeEnum;
use App\Http\Controllers\Admin\Concerns\InteractsWithCatalogModals;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Catalog\FaqRequest;
use App\Services\Admin\Catalog\FaqService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class FaqController extends Controller
{
    use InteractsWithCatalogModals;

    public function __construct(
        private readonly FaqService $service,
        private readonly DataTables $dataTables,
    ) {}

    public function index(Request $request): View|JsonResponse
    {
        if ($request->ajax()) {
            $type = $request->query('type');
            $accountType = is_string($type) && $type !== '' ? AccountTypeEnum::tryFrom($type) : null;

            return $this->dataTables->eloquent($this->service->dataTableQuery(app()->getLocale(), $accountType))
                ->addColumn('type_label', fn ($faq): string => __('admin.faqs.types.'.$faq->type->value))
                ->addColumn('actions', fn ($faq): string => view(
                    'admin.catalog.parts.actions',
                    $this->actionUrls('faqs', (int) $faq->id),
                )->render())
                ->rawColumns(['actions'])
                ->toJson();
        }

        return view('admin.catalog.index', [
            'oneObjectTitle' => __('admin.faqs.title'),
            'pageDescription' => __('admin.faqs.description'),
            'createLabel' => __('admin.faqs.create'),
            'createUrl' => route('admin.faqs.create'),
            'dataUrl' => route('admin.faqs.index'),
            'columns' => [
                ['data' => 'id', 'name' => 'faqs.id', 'title' => '#'],
                ['data' => 'question', 'name' => 'faq_translation.question', 'title' => __('admin.faqs.fields.question')],
                ['data' => 'answer', 'name' => 'faq_translation.answer', 'title' => __('admin.faqs.fields.answer')],
                ['data' => 'type_label', 'name' => 'faqs.type', 'title' => __('admin.faqs.fields.type')],
                ['data' => 'actions', 'name' => 'actions', 'title' => __('admin.catalog.fields.actions'), 'orderable' => false, 'searchable' => false],
            ],
            'filters' => $this->accountTypeFilter($request),
        ]);
    }

    public function create(): never
    {
        $this->modal('admin.faqs.parts.form', [
            'faq' => null,
            'action' => route('admin.faqs.store'),
            'method' => 'POST',
        ]);
    }

    public function store(FaqRequest $request): never
    {
        $this->service->create($request->validated());
        $this->catalogSuccess(__('admin.catalog.messages.created'));
    }

    public function show(int $faq): never
    {
        $this->modal('admin.faqs.parts.show', ['faq' => $this->service->find($faq)]);
    }

    public function edit(int $faq): never
    {
        $this->modal('admin.faqs.parts.form', [
            'faq' => $this->service->find($faq),
            'action' => route('admin.faqs.update', $faq),
            'method' => 'PUT',
        ]);
    }

    public function update(FaqRequest $request, int $faq): never
    {
        $this->service->update($faq, $request->validated());
        $this->catalogSuccess(__('admin.catalog.messages.updated'));
    }

    public function destroy(int $faq): never
    {
        $this->service->delete($faq);
        $this->catalogSuccess(__('admin.catalog.messages.deleted'));
    }
}
