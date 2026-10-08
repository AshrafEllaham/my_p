<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SettingsRequest;
use App\Services\Admin\SettingsService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;

class SettingsController extends Controller
{
    public function __construct(private readonly SettingsService $service) {}

    public function edit(): View
    {
        $settings = $this->service->get();

        return view('admin.settings.edit', [
            'settings' => $settings,
            'imageUrls' => collect(['fav_icon', 'logo_header', 'logo_footer'])
                ->mapWithKeys(fn (string $field): array => [
                    $field => $settings?->{$field}
                        ? Storage::disk('public')->url($settings->{$field})
                        : '',
                ]),
            'oneObjectTitle' => __('admin.site_settings.title'),
            'breadcrumbs' => [['label' => __('admin.site_settings.title')]],
        ]);
    }

    public function update(SettingsRequest $request): RedirectResponse
    {
        $this->service->save($request->validated());

        return redirect()
            ->route('admin.settings.edit')
            ->with('success', __('admin.site_settings.messages.updated'));
    }
}
