<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Profile\UpdateAdminProfileRequest;
use App\Models\Admin\Admin;
use App\Services\Admin\AdminProfileService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class ProfileController extends Controller
{
    private string $path = 'admin.profile';

    public function __construct(
        private readonly AdminProfileService $service,
    ) {}

    public function edit(): View
    {
        /** @var Admin $admin */
        $admin = auth('admin')->user();
        $oneObjectTitle = __('admin.profile.title');
        $breadcrumbs = [
            ['label' => $oneObjectTitle],
        ];

        return view("{$this->path}.edit", compact('admin', 'oneObjectTitle', 'breadcrumbs'));
    }

    public function update(UpdateAdminProfileRequest $request): RedirectResponse
    {
        /** @var Admin $admin */
        $admin = auth('admin')->user();
        $this->service->update($admin, $request->validated());

        return redirect()
            ->route('admin.profile.edit')
            ->with('success', __('admin.profile.updated'));
    }
}
