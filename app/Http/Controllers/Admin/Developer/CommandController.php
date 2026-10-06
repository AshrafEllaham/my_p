<?php

namespace App\Http\Controllers\Admin\Developer;

use App\Http\Controllers\Controller;
use App\Services\Admin\DeveloperToolsService;
use Illuminate\Contracts\View\View;

class CommandController extends Controller
{
    public function __construct(private readonly DeveloperToolsService $service) {}

    public function index(): View
    {
        return view('admin.developer.commands.index', [
            'commands' => $this->service->getCommands(),
        ]);
    }
}
