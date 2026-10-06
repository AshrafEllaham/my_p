<?php

namespace App\Http\Controllers\Admin\Developer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Developer\SaveDeveloperCommandRequest;
use App\Services\Admin\DeveloperToolsService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class CommandController extends Controller
{
    public function __construct(private readonly DeveloperToolsService $service) {}

    public function index(): View
    {
        return view('admin.developer.commands.index', [
            'commands' => $this->service->getCommands(),
        ]);
    }

    public function store(SaveDeveloperCommandRequest $request): RedirectResponse
    {
        $this->service->createCommand($request->validated()['command']);

        return redirect()->route('admin.developer.commands.index')
            ->with('success', __('admin.developer_tools.command_created'));
    }

    public function update(SaveDeveloperCommandRequest $request, int $command): RedirectResponse
    {
        $this->service->updateCommand($command, $request->validated()['command']);

        return redirect()->route('admin.developer.commands.index')
            ->with('success', __('admin.developer_tools.command_updated'));
    }

    public function destroy(int $command): RedirectResponse
    {
        $this->service->deleteCommand($command);

        return redirect()->route('admin.developer.commands.index')
            ->with('success', __('admin.developer_tools.command_deleted'));
    }
}
