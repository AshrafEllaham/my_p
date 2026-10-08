<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\Sai\SettingsService;
use Illuminate\Contracts\View\View;

class HomeController extends Controller
{
    public function __construct(private readonly SettingsService $settingsService) {}

    public function privacy(): View
    {
        return $this->settingsPage('web.privacy');
    }

    public function terms_conditions(): View
    {
        return $this->settingsPage('web.terms_conditions');
    }

    public function about_app(): View
    {
        return $this->settingsPage('web.about_app');
    }

    private function settingsPage(string $view): View
    {
        return view($view, ['settings' => $this->settingsService->get()]);
    }

    public function pay_online($id, $type)
    {
        return view('payments.payment', ['id' => $id, 'type' => $type]);
    }

    public function paymentSuccess($id, $type)
    {

        return view('payments.payment_success');
    }

    public function paymentFailed($id, $type)
    {
        return view('payments.payment_failed');
    }
}
