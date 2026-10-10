<?php

namespace App\Http\Controllers\Web;

use App\Enums\PaymentOperationTableEnum;
use App\Enums\PaymentStatusEnum;
use App\Http\Controllers\Controller;
use App\Services\Sai\AdService;
use App\Services\Sai\SettingsService;
use Illuminate\Contracts\View\View;

class HomeController extends Controller
{
    public function __construct(
        private readonly SettingsService $settingsService,
        private readonly AdService $adService,
    ) {}

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

    public function paymentSuccess(int $id, string $type): View
    {
        if (PaymentOperationTableEnum::tryFrom($type) === PaymentOperationTableEnum::PayTrip) {
            $status = $this->adService->completeOnlineAdPayment($id, PaymentOperationTableEnum::PayTrip);

            return view($status === PaymentStatusEnum::Paid ? 'payments.payment_success' : 'payments.payment_failed');
        }

        return view('payments.payment_success');
    }

    public function paymentFailed(int $id, string $type): View
    {
        if (PaymentOperationTableEnum::tryFrom($type) === PaymentOperationTableEnum::PayTrip) {
            $status = $this->adService->failOnlineAdPayment($id, PaymentOperationTableEnum::PayTrip);

            return view($status === PaymentStatusEnum::Paid ? 'payments.payment_success' : 'payments.payment_failed');
        }

        return view('payments.payment_failed');
    }
}
