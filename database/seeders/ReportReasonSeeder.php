<?php

namespace Database\Seeders;

use App\Enums\UserReportTypeEnum;
use App\Repositories\Sai\ReportReasonRepository;
use App\Services\Admin\Catalog\ReportReasonService;
use Illuminate\Database\Seeder;

class ReportReasonSeeder extends Seeder
{
    public function run(ReportReasonRepository $reasons, ReportReasonService $service): void
    {
        foreach ([
            ['type' => UserReportTypeEnum::Product, 'ar' => 'معلومات أو سعر غير صحيح', 'en' => 'Incorrect information or price'],
            ['type' => UserReportTypeEnum::Product, 'ar' => 'منتج مخالف أو غير مسموح', 'en' => 'Prohibited or non-compliant product'],
            ['type' => UserReportTypeEnum::Product, 'ar' => 'منتج مقلّد', 'en' => 'Counterfeit product'],
            ['type' => UserReportTypeEnum::Store, 'ar' => 'معلومات المتجر غير صحيحة', 'en' => 'Incorrect store information'],
            ['type' => UserReportTypeEnum::Store, 'ar' => 'متجر مخالف أو غير موثوق', 'en' => 'Prohibited or untrusted store'],
            ['type' => UserReportTypeEnum::Store, 'ar' => 'انتحال هوية متجر', 'en' => 'Store impersonation'],
        ] as $reason) {
            if (! $reasons->hasEnglishTitle($reason['type'], $reason['en'])) {
                $service->create([
                    'type' => $reason['type']->value,
                    'ar' => ['title' => $reason['ar']],
                    'en' => ['title' => $reason['en']],
                ]);
            }
        }
    }
}
