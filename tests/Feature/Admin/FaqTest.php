<?php

namespace Tests\Feature\Admin;

use App\Enums\AccountTypeEnum;
use App\Models\Admin\Admin;
use App\Models\Sai\Faq;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mcamara\LaravelLocalization\Middleware\LaravelLocalizationRedirectFilter;
use Mcamara\LaravelLocalization\Middleware\LocaleSessionRedirect;
use Tests\TestCase;

class FaqTest extends TestCase
{
    use RefreshDatabase;

    private Admin $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        $this->withoutMiddleware([
            LaravelLocalizationRedirectFilter::class,
            LocaleSessionRedirect::class,
        ]);

        $this->admin = Admin::factory()->create();
    }

    public function test_faqs_page_requires_admin_authentication(): void
    {
        $this->get(route('admin.faqs.index'))->assertRedirect(route('admin.login'));
    }

    public function test_authenticated_admin_can_open_faqs_page_and_see_type_filter_tabs(): void
    {
        $response = $this->actingAs($this->admin, 'admin')
            ->get(route('admin.faqs.index'))
            ->assertOk()
            ->assertViewIs('admin.catalog.index')
            ->assertSee('data-catalog-filters', false)
            ->assertSee(__('admin.faqs.filters.all_types'))
            ->assertSee(__('admin.faqs.types.store'))
            ->assertSee(__('admin.faqs.types.user'));

        $content = $response->getContent();
        $this->assertStringContainsString('name="type"', $content);
        $this->assertStringContainsString('data-filter-control', $content);
        $this->assertStringContainsString('value="" data-filter-control checked', $content);
    }

    public function test_faqs_page_with_type_query_marks_corresponding_filter_tab_as_active(): void
    {
        $response = $this->actingAs($this->admin, 'admin')
            ->get(route('admin.faqs.index', ['type' => AccountTypeEnum::Store->value]))
            ->assertOk();

        $content = $response->getContent();
        $this->assertStringContainsString('value="store" data-filter-control checked', $content);
    }

    public function test_datatable_returns_all_faqs_when_no_filter_is_applied(): void
    {
        $this->createFaq(AccountTypeEnum::Store, 'سؤال المتجر 1', 'Store Question 1');
        $this->createFaq(AccountTypeEnum::Store, 'سؤال المتجر 2', 'Store Question 2');
        $this->createFaq(AccountTypeEnum::User, 'سؤال المستخدم 1', 'User Question 1');

        $response = $this->actingAs($this->admin, 'admin')
            ->withHeader('X-Requested-With', 'XMLHttpRequest')
            ->getJson(route('admin.faqs.index', [
                'draw' => 1,
                'start' => 0,
                'length' => 10,
                'columns' => [
                    ['data' => 'id', 'name' => 'faqs.id', 'searchable' => 'true', 'orderable' => 'true', 'search' => ['value' => '', 'regex' => 'false']],
                    ['data' => 'question', 'name' => 'faq_translation.question', 'searchable' => 'true', 'orderable' => 'true', 'search' => ['value' => '', 'regex' => 'false']],
                    ['data' => 'type_label', 'name' => 'faqs.type', 'searchable' => 'true', 'orderable' => 'true', 'search' => ['value' => '', 'regex' => 'false']],
                ],
                'order' => [['column' => 0, 'dir' => 'desc']],
            ]));

        $response->assertOk()
            ->assertJsonPath('recordsTotal', 3)
            ->assertJsonPath('recordsFiltered', 3)
            ->assertJsonCount(3, 'data');
    }

    public function test_datatable_filters_faqs_by_user_type(): void
    {
        $this->createFaq(AccountTypeEnum::Store, 'سؤال المتجر 1', 'Store Question 1');
        $this->createFaq(AccountTypeEnum::Store, 'سؤال المتجر 2', 'Store Question 2');
        $this->createFaq(AccountTypeEnum::User, 'سؤال المستخدم 1', 'User Question 1');
        $this->createFaq(AccountTypeEnum::User, 'سؤال المستخدم 2', 'User Question 2');
        $this->createFaq(AccountTypeEnum::User, 'سؤال المستخدم 3', 'User Question 3');

        $response = $this->actingAs($this->admin, 'admin')
            ->withHeader('X-Requested-With', 'XMLHttpRequest')
            ->getJson(route('admin.faqs.index', [
                'draw' => 1,
                'start' => 0,
                'length' => 10,
                'type' => AccountTypeEnum::User->value,
                'columns' => [
                    ['data' => 'id', 'name' => 'faqs.id', 'searchable' => 'true', 'orderable' => 'true', 'search' => ['value' => '', 'regex' => 'false']],
                    ['data' => 'question', 'name' => 'faq_translation.question', 'searchable' => 'true', 'orderable' => 'true', 'search' => ['value' => '', 'regex' => 'false']],
                    ['data' => 'type_label', 'name' => 'faqs.type', 'searchable' => 'true', 'orderable' => 'true', 'search' => ['value' => '', 'regex' => 'false']],
                ],
                'order' => [['column' => 0, 'dir' => 'desc']],
            ]));

        $response->assertOk()
            ->assertJsonPath('recordsTotal', 3)
            ->assertJsonPath('recordsFiltered', 3)
            ->assertJsonCount(3, 'data');

        $data = $response->json('data');
        foreach ($data as $row) {
            $this->assertSame(__('admin.faqs.types.user'), $row['type_label']);
        }
    }

    public function test_datatable_filters_faqs_by_store_type(): void
    {
        $this->createFaq(AccountTypeEnum::Store, 'سؤال المتجر 1', 'Store Question 1');
        $this->createFaq(AccountTypeEnum::Store, 'سؤال المتجر 2', 'Store Question 2');
        $this->createFaq(AccountTypeEnum::User, 'سؤال المستخدم 1', 'User Question 1');

        $response = $this->actingAs($this->admin, 'admin')
            ->withHeader('X-Requested-With', 'XMLHttpRequest')
            ->getJson(route('admin.faqs.index', [
                'draw' => 1,
                'start' => 0,
                'length' => 10,
                'type' => AccountTypeEnum::Store->value,
                'columns' => [
                    ['data' => 'id', 'name' => 'faqs.id', 'searchable' => 'true', 'orderable' => 'true', 'search' => ['value' => '', 'regex' => 'false']],
                    ['data' => 'question', 'name' => 'faq_translation.question', 'searchable' => 'true', 'orderable' => 'true', 'search' => ['value' => '', 'regex' => 'false']],
                    ['data' => 'type_label', 'name' => 'faqs.type', 'searchable' => 'true', 'orderable' => 'true', 'search' => ['value' => '', 'regex' => 'false']],
                ],
                'order' => [['column' => 0, 'dir' => 'desc']],
            ]));

        $response->assertOk()
            ->assertJsonPath('recordsTotal', 2)
            ->assertJsonPath('recordsFiltered', 2)
            ->assertJsonCount(2, 'data');

        $data = $response->json('data');
        foreach ($data as $row) {
            $this->assertSame(__('admin.faqs.types.store'), $row['type_label']);
        }
    }

    public function test_admin_can_create_update_and_delete_faq_with_translations(): void
    {
        $payload = [
            'type' => AccountTypeEnum::User->value,
            'ar' => [
                'question' => 'كيف أسجل في سعي؟',
                'answer' => 'عبر رقم الهاتف وتأكيد الرمز.',
            ],
            'en' => [
                'question' => 'How to register on Saey?',
                'answer' => 'Using phone number and OTP confirmation.',
            ],
        ];

        $createResponse = $this->actingAs($this->admin, 'admin')->postJson(route('admin.faqs.store'), $payload);
        $createResponse->assertOk()->assertJsonPath('status', true);

        $faq = Faq::query()->firstOrFail();
        $this->assertSame(AccountTypeEnum::User, $faq->type);
        $this->assertDatabaseHas('faq_translations', [
            'faq_id' => $faq->id,
            'locale' => 'ar',
            'question' => 'كيف أسجل في سعي؟',
        ]);
        $this->assertDatabaseHas('faq_translations', [
            'faq_id' => $faq->id,
            'locale' => 'en',
            'question' => 'How to register on Saey?',
        ]);

        $updatePayload = [
            'type' => AccountTypeEnum::Store->value,
            'ar' => [
                'question' => 'كيف أسجل كمتجر؟',
                'answer' => 'عبر إكمال بيانات المتجر.',
            ],
            'en' => [
                'question' => 'How to register as store?',
                'answer' => 'By completing the store profile.',
            ],
        ];

        $updateResponse = $this->actingAs($this->admin, 'admin')->putJson(route('admin.faqs.update', $faq), $updatePayload);
        $updateResponse->assertOk()->assertJsonPath('status', true);

        $faq->refresh();
        $this->assertSame(AccountTypeEnum::Store, $faq->type);
        $this->assertDatabaseHas('faq_translations', [
            'faq_id' => $faq->id,
            'locale' => 'ar',
            'question' => 'كيف أسجل كمتجر؟',
        ]);

        $deleteResponse = $this->actingAs($this->admin, 'admin')->deleteJson(route('admin.faqs.destroy', $faq));
        $deleteResponse->assertOk()->assertJsonPath('status', true);

        $this->assertDatabaseMissing('faqs', ['id' => $faq->id]);
        $this->assertDatabaseMissing('faq_translations', ['faq_id' => $faq->id]);
    }

    private function createFaq(AccountTypeEnum $type, string $questionAr, string $questionEn): Faq
    {
        return Faq::query()->create([
            'type' => $type,
            'ar' => [
                'question' => $questionAr,
                'answer' => 'إجابة تجريبية',
            ],
            'en' => [
                'question' => $questionEn,
                'answer' => 'Test answer',
            ],
        ]);
    }
}
