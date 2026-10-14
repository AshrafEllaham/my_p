<?php

namespace Tests\Feature\Admin;

use App\Enums\UserReportTypeEnum;
use App\Models\Admin\Admin;
use App\Models\Sai\ReportReason;
use App\Models\Sai\UserReport;
use Database\Factories\UserFactory;
use Database\Seeders\ReportReasonSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mcamara\LaravelLocalization\Middleware\LaravelLocalizationRedirectFilter;
use Mcamara\LaravelLocalization\Middleware\LocaleSessionRedirect;
use Tests\TestCase;

class ReportReasonTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        $this->withoutMiddleware([LaravelLocalizationRedirectFilter::class, LocaleSessionRedirect::class]);
    }

    public function test_report_reason_crud_requires_admin_and_manages_both_translations(): void
    {
        $this->get(route('admin.report-reasons.index'))->assertRedirect(route('admin.login'));
        $admin = Admin::factory()->create();
        $this->actingAs($admin, 'admin')->get(route('admin.report-reasons.index'))
            ->assertOk()->assertViewIs('admin.catalog.index');

        $payload = [
            'type' => UserReportTypeEnum::Product->value,
            'ar' => ['title' => 'سعر غير صحيح'],
            'en' => ['title' => 'Incorrect price'],
        ];
        $this->postJson(route('admin.report-reasons.store'), $payload)->assertOk()->assertJsonPath('status', true);
        $reason = ReportReason::query()->firstOrFail();
        $this->assertSame(UserReportTypeEnum::Product, $reason->type);
        $this->assertDatabaseHas('report_reason_translations', ['report_reason_id' => $reason->id, 'locale' => 'ar', 'title' => 'سعر غير صحيح']);
        $this->assertDatabaseHas('report_reason_translations', ['report_reason_id' => $reason->id, 'locale' => 'en', 'title' => 'Incorrect price']);

        $this->getJson(route('admin.report-reasons.edit', $reason))->assertOk();
        $this->getJson(route('admin.report-reasons.show', $reason))->assertOk();
        $this->putJson(route('admin.report-reasons.update', $reason), [
            'type' => UserReportTypeEnum::Store->value,
            'ar' => ['title' => 'منتج مقلّد'], 'en' => ['title' => 'Counterfeit product'],
        ])->assertOk();
        $this->assertDatabaseHas('report_reasons', ['id' => $reason->id, 'type' => UserReportTypeEnum::Store->value]);
        $this->assertDatabaseHas('report_reason_translations', ['report_reason_id' => $reason->id, 'locale' => 'ar', 'title' => 'منتج مقلّد']);

        $this->deleteJson(route('admin.report-reasons.destroy', $reason))->assertOk();
        $this->assertDatabaseMissing('report_reasons', ['id' => $reason->id]);
    }

    public function test_report_reason_requires_both_titles(): void
    {
        $admin = Admin::factory()->create();
        $this->actingAs($admin, 'admin')->postJson(route('admin.report-reasons.store'), ['ar' => ['title' => 'سبب']])
            ->assertUnprocessable()->assertJsonValidationErrors(['type', 'en.title']);
        $this->postJson(route('admin.report-reasons.store'), [
            'type' => 'invalid',
            'ar' => ['title' => 'سبب'],
            'en' => ['title' => 'Reason'],
        ])->assertUnprocessable()->assertJsonValidationErrors('type');
    }

    public function test_seeded_reasons_appear_in_datatable_and_used_reason_cannot_be_deleted(): void
    {
        $this->seed(ReportReasonSeeder::class);
        $this->seed(ReportReasonSeeder::class);
        $this->assertDatabaseCount('report_reasons', 6);

        $admin = Admin::factory()->create();
        $reason = ReportReason::query()->firstOrFail();
        $this->actingAs($admin, 'admin')->withHeader('X-Requested-With', 'XMLHttpRequest')
            ->getJson(route('admin.report-reasons.index', [
                'draw' => 1, 'start' => 0, 'length' => 10,
                'columns' => [
                    ['data' => 'id', 'name' => 'report_reasons.id', 'searchable' => 'true', 'orderable' => 'true', 'search' => ['value' => '', 'regex' => 'false']],
                    ['data' => 'type', 'name' => 'report_reasons.type', 'searchable' => 'true', 'orderable' => 'true', 'search' => ['value' => '', 'regex' => 'false']],
                    ['data' => 'title', 'name' => 'reason_translation.title', 'searchable' => 'true', 'orderable' => 'true', 'search' => ['value' => '', 'regex' => 'false']],
                ],
                'order' => [['column' => 0, 'dir' => 'desc']],
            ]))->assertOk()->assertJsonPath('recordsTotal', 6);

        $user = UserFactory::new()->create();
        UserReport::query()->create([
            'user_id' => $user->id,
            'type' => UserReportTypeEnum::Product,
            'item_id' => 123,
            'report_reason_id' => $reason->id,
        ]);
        $this->deleteJson(route('admin.report-reasons.destroy', $reason))->assertUnprocessable();
        $this->assertDatabaseHas('report_reasons', ['id' => $reason->id]);
    }
}
