<?php

namespace App\Http\Controllers\Admin\Developer;

use App\Http\Controllers\Admin\Concerns\InteractsWithCatalogModals;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Developer\SaveDeveloperCommandRequest;
use App\Services\Admin\DeveloperToolsService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class CommandController extends Controller
{
    use InteractsWithCatalogModals;

    public function __construct(
        private readonly DeveloperToolsService $service,
        private readonly DataTables $dataTables,
    ) {}

    public function index(Request $request): View|JsonResponse
    {
        if ($request->ajax()) {
            return $this->dataTables->eloquent($this->service->commandListQuery())
                ->editColumn('command', fn ($command): string => '<code dir="ltr">'.e($command->command).'</code>')
                ->addColumn('actions', fn ($command): string => view('admin.developer.commands.parts.actions', [
                    'command' => $command,
                ])->render())
                ->rawColumns(['command', 'actions'])
                ->toJson();
        }

        return view('admin.developer.commands.index');
    }

    public function create(): never
    {
        $this->modal('admin.developer.commands.parts.form', [
            'command' => null,
            'action' => route('admin.developer.commands.store'),
            'method' => 'POST',
        ]);
    }

    public function edit(int $command): never
    {
        $this->modal('admin.developer.commands.parts.form', [
            'command' => $this->service->findCommand($command),
            'action' => route('admin.developer.commands.update', $command),
            'method' => 'PUT',
        ]);
    }

    public function store(SaveDeveloperCommandRequest $request): RedirectResponse
    {
        $this->service->createCommand($request->validated()['command']);

        if ($request->expectsJson()) {
            $this->catalogSuccess(__('admin.developer_tools.command_created'));
        }

        return redirect()->route('admin.developer.commands.index')
            ->with('success', __('admin.developer_tools.command_created'));
    }

    public function update(SaveDeveloperCommandRequest $request, int $command): RedirectResponse
    {
        $this->service->updateCommand($command, $request->validated()['command']);

        if ($request->expectsJson()) {
            $this->catalogSuccess(__('admin.developer_tools.command_updated'));
        }

        return redirect()->route('admin.developer.commands.index')
            ->with('success', __('admin.developer_tools.command_updated'));
    }

    public function destroy(Request $request, int $command): RedirectResponse
    {
        $this->service->deleteCommand($command);

        if ($request->expectsJson()) {
            $this->catalogSuccess(__('admin.developer_tools.command_deleted'));
        }

        return redirect()->route('admin.developer.commands.index')
            ->with('success', __('admin.developer_tools.command_deleted'));
    }
}
