<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Admin\HomeService;
use Illuminate\Contracts\View\View;

class HomeController extends Controller
{
    private string $path = 'admin.home';

    public function __construct(
        private readonly HomeService $service,
    ) {}

    public function index(): View
    {
        $oneObjectTitle = __('admin.dashboard');
        $dashboard = $this->service->getDashboardData();

        return view("{$this->path}.index", compact('oneObjectTitle', 'dashboard'));
    }
}
