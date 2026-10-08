<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Admin\ContactUsInboxService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class ContactUsController extends Controller
{
    public function __construct(
        private readonly ContactUsInboxService $service,
        private readonly DataTables $dataTables,
    ) {}

    public function index(Request $request): View|JsonResponse
    {
        if ($request->ajax()) {
            return $this->dataTables
                ->eloquent($this->service->dataTableQuery())
                ->addColumn('actions', fn ($contact): string => view('admin.contact-us.parts.actions', [
                    'deleteUrl' => route('admin.contact-us.destroy', $contact->id),
                ])->render())
                ->rawColumns(['actions'])
                ->toJson();
        }

        return view('admin.contact-us.index', [
            'oneObjectTitle' => __('admin.contact_us.title'),
            'columns' => [
                ['data' => 'id', 'name' => 'contact_us.id', 'title' => '#'],
                ['data' => 'name', 'name' => 'contact_us.name', 'title' => __('admin.contact_us.fields.name')],
                ['data' => 'email', 'name' => 'contact_us.email', 'title' => __('admin.contact_us.fields.email')],
                ['data' => 'subject', 'name' => 'contact_us.subject', 'title' => __('admin.contact_us.fields.subject')],
                ['data' => 'message', 'name' => 'contact_us.message', 'title' => __('admin.contact_us.fields.message'), 'className' => 'admin-contact-messages__message'],
                ['data' => 'actions', 'name' => 'actions', 'title' => __('admin.catalog.fields.actions'), 'orderable' => false, 'searchable' => false],
            ],
        ]);
    }

    public function destroy(int $contactUs): never
    {
        $this->service->delete($contactUs);

        $this->dashBoardJson(200, __('admin.catalog.messages.deleted'));
    }
}
