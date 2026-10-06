<?php

namespace App\Http\Controllers\Admin\Developer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\RunDeveloperCommandRequest;
use App\Services\Admin\DeveloperToolsService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;

class TerminalController extends Controller
{
    public function __construct(private readonly DeveloperToolsService $service) {}

    public function index(): View
    {
        return view('admin.developer.terminal.index', [
            'commands' => $this->service->getCommands(),
        ]);
    }

    public function run(RunDeveloperCommandRequest $request): JsonResponse
    {
        $result = $this->service->run($request->validated()['command']);

        return response()->json($result, $result['successful'] ? 200 : 422);
    }
}
