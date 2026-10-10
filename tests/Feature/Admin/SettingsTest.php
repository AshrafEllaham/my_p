<?php

namespace Tests\Feature\Admin;

use App\Models\Admin\Admin;
use App\Models\Sai\Settings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Mcamara\LaravelLocalization\Middleware\LaravelLocalizationRedirectFilter;
use Mcamara\LaravelLocalization\Middleware\LocaleSessionRedirect;
use Tests\TestCase;

class SettingsTest extends TestCase
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

    public function test_settings_page_requires_admin_authentication(): void
    {
        $this->get(route('admin.settings.edit'))->assertRedirect(route('admin.login'));
    }

    public function test_authenticated_admin_can_open_settings_page_and_see_dropify_inputs(): void
    {
        $response = $this->actingAs($this->admin, 'admin')
            ->get(route('admin.settings.edit'))
            ->assertOk()
            ->assertViewIs('admin.settings.edit')
            ->assertSee('admin-settings-images-grid')
            ->assertSee('class="dropify', false)
            ->assertSee('name="fav_icon"', false)
            ->assertSee('name="logo_header"', false)
            ->assertSee('name="logo_footer"', false)
            ->assertSee('name="ad_home_price"', false)
            ->assertSee('name="ad_category_price"', false)
            ->assertSee('data-max-file-size="5M"', false)
            ->assertSee('data-height="180"', false)
            ->assertSee(__('admin.site_settings.drop_file'))
            ->assertSee(__('admin.site_settings.replace_file'))
            ->assertSee(__('admin.site_settings.clear_file'))
            ->assertSee(__('admin.site_settings.no_image'));

        $content = $response->getContent();
        $this->assertStringContainsString('id="fav_icon"', $content);
        $this->assertStringContainsString('id="logo_header"', $content);
        $this->assertStringContainsString('id="logo_footer"', $content);
    }

    public function test_settings_page_displays_configured_image_preview_url_and_badge(): void
    {
        Storage::fake('public');

        $settings = Settings::updateOrCreate(
            ['id' => 1],
            [
                'fav_icon' => 'settings/test-favicon.png',
                'logo_header' => 'settings/test-header.png',
                'logo_footer' => 'settings/test-footer.png',
                'ar' => [
                    'website_name' => 'سعي',
                    'about_app' => 'عن التطبيق',
                    'privacy' => 'سياسة الخصوصية',
                    'terms_conditions' => 'الشروط والأحكام',
                ],
                'en' => [
                    'website_name' => 'Saey',
                    'about_app' => 'About app',
                    'privacy' => 'Privacy policy',
                    'terms_conditions' => 'Terms and conditions',
                ],
            ]
        );

        $expectedFaviconUrl = Storage::disk('public')->url($settings->fav_icon);
        $expectedHeaderUrl = Storage::disk('public')->url($settings->logo_header);
        $expectedFooterUrl = Storage::disk('public')->url($settings->logo_footer);

        $this->actingAs($this->admin, 'admin')
            ->get(route('admin.settings.edit'))
            ->assertOk()
            ->assertSee('data-default-file="'.$expectedFaviconUrl.'"', false)
            ->assertSee('data-default-file="'.$expectedHeaderUrl.'"', false)
            ->assertSee('data-default-file="'.$expectedFooterUrl.'"', false)
            ->assertSee(__('admin.site_settings.current_image'));
    }

    public function test_admin_can_update_settings_and_upload_new_images_with_dropify(): void
    {
        Storage::fake('public');

        $favicon = UploadedFile::fake()->image('favicon.png', 64, 64);
        $headerLogo = UploadedFile::fake()->image('header.png', 400, 120);
        $footerLogo = UploadedFile::fake()->image('footer.webp', 300, 80);

        $payload = [
            'fav_icon' => $favicon,
            'logo_header' => $headerLogo,
            'logo_footer' => $footerLogo,
            'whatsapp' => '+966500000000',
            'phone' => '+966500000001',
            'email' => 'admin@saey.test',
            'facebook' => 'https://facebook.com/saey',
            'instagram' => 'https://instagram.com/saey',
            'ad_home_price' => '125.50',
            'ad_category_price' => '75.00',
            'ar' => [
                'website_name' => 'سعي',
                'about_app' => 'تطبيق سعي هو منصة متكاملة للخدمات.',
                'privacy' => 'سياسة الخصوصية باللغة العربية.',
                'terms_conditions' => 'الشروط والأحكام باللغة العربية.',
            ],
            'en' => [
                'website_name' => 'Saey',
                'about_app' => 'Saey app is a comprehensive platform.',
                'privacy' => 'Privacy policy in English.',
                'terms_conditions' => 'Terms and conditions in English.',
            ],
        ];

        $this->actingAs($this->admin, 'admin')
            ->put(route('admin.settings.update'), $payload)
            ->assertRedirect(route('admin.settings.edit'))
            ->assertSessionHas('success', __('admin.site_settings.messages.updated'));

        $settings = Settings::first();
        $this->assertNotNull($settings);
        $this->assertNotNull($settings->fav_icon);
        $this->assertNotNull($settings->logo_header);
        $this->assertNotNull($settings->logo_footer);

        Storage::disk('public')->assertExists($settings->fav_icon);
        Storage::disk('public')->assertExists($settings->logo_header);
        Storage::disk('public')->assertExists($settings->logo_footer);

        $this->assertSame('+966500000000', $settings->whatsapp);
        $this->assertSame('admin@saey.test', $settings->email);
        $this->assertSame('125.50', $settings->ad_home_price);
        $this->assertSame('75.00', $settings->ad_category_price);
        $this->assertSame('تطبيق سعي هو منصة متكاملة للخدمات.', $settings->translate('ar')->about_app);
        $this->assertSame('Saey app is a comprehensive platform.', $settings->translate('en')->about_app);
    }

    public function test_ad_prices_are_required_and_cannot_be_negative(): void
    {
        $payload = [
            'ad_home_price' => '-1',
            'ad_category_price' => '50.00',
            'ar' => [
                'website_name' => 'سعي',
                'about_app' => 'عن التطبيق',
                'privacy' => 'سياسة الخصوصية',
                'terms_conditions' => 'الشروط والأحكام',
            ],
            'en' => [
                'website_name' => 'Saey',
                'about_app' => 'About app',
                'privacy' => 'Privacy policy',
                'terms_conditions' => 'Terms and conditions',
            ],
        ];

        $this->actingAs($this->admin, 'admin')
            ->from(route('admin.settings.edit'))
            ->put(route('admin.settings.update'), $payload)
            ->assertRedirect(route('admin.settings.edit'))
            ->assertSessionHasErrors(['ad_home_price']);

        $this->assertSame(
            __('messages.validation.settings.ad_pricing.min'),
            session('errors')->first('ad_home_price')
        );
    }

    public function test_existing_images_are_preserved_when_updating_without_new_files(): void
    {
        Storage::fake('public');

        $initialFavicon = UploadedFile::fake()->image('old-fav.png', 32, 32);
        $path = Storage::disk('public')->putFile('settings', $initialFavicon);

        $settings = Settings::updateOrCreate(
            ['id' => 1],
            [
                'fav_icon' => $path,
                'phone' => '11111111',
                'ar' => [
                    'website_name' => 'سعي',
                    'about_app' => 'عن التطبيق',
                    'privacy' => 'الخصوصية',
                    'terms_conditions' => 'الشروط',
                ],
                'en' => [
                    'website_name' => 'Saey',
                    'about_app' => 'About',
                    'privacy' => 'Privacy',
                    'terms_conditions' => 'Terms',
                ],
            ]
        );

        $updatePayload = [
            'phone' => '99999999',
            'ad_home_price' => '200.00',
            'ad_category_price' => '80.00',
            'ar' => [
                'website_name' => 'سعي محدث',
                'about_app' => 'عن التطبيق المحدث',
                'privacy' => 'الخصوصية المحدثة',
                'terms_conditions' => 'الشروط المحدثة',
            ],
            'en' => [
                'website_name' => 'Saey updated',
                'about_app' => 'About updated',
                'privacy' => 'Privacy updated',
                'terms_conditions' => 'Terms updated',
            ],
        ];

        $this->actingAs($this->admin, 'admin')
            ->put(route('admin.settings.update'), $updatePayload)
            ->assertRedirect(route('admin.settings.edit'));

        $settings->refresh();
        $this->assertSame($path, $settings->fav_icon);
        $this->assertSame('99999999', $settings->phone);
        $this->assertSame('200.00', $settings->ad_home_price);
        Storage::disk('public')->assertExists($path);
    }

    public function test_uploading_oversized_image_fails_validation(): void
    {
        Storage::fake('public');

        // Max is 5120 KB = 5MB. 6000 KB should fail validation.
        $oversizedFile = UploadedFile::fake()->create('huge.png', 6000, 'image/png');

        $payload = [
            'fav_icon' => $oversizedFile,
            'ad_home_price' => '100.00',
            'ad_category_price' => '50.00',
            'ar' => [
                'website_name' => 'سعي',
                'about_app' => 'عن التطبيق',
                'privacy' => 'سياسة الخصوصية',
                'terms_conditions' => 'الشروط والأحكام',
            ],
            'en' => [
                'website_name' => 'Saey',
                'about_app' => 'About app',
                'privacy' => 'Privacy policy',
                'terms_conditions' => 'Terms and conditions',
            ],
        ];

        $this->actingAs($this->admin, 'admin')
            ->from(route('admin.settings.edit'))
            ->put(route('admin.settings.update'), $payload)
            ->assertRedirect(route('admin.settings.edit'))
            ->assertSessionHasErrors(['fav_icon']);

        $this->assertSame(
            __('messages.validation.settings.image.max'),
            session('errors')->first('fav_icon')
        );
    }

    public function test_uploading_invalid_file_extension_fails_validation(): void
    {
        Storage::fake('public');

        $invalidFile = UploadedFile::fake()->create('document.pdf', 100, 'application/pdf');

        $payload = [
            'logo_header' => $invalidFile,
            'ad_home_price' => '100.00',
            'ad_category_price' => '50.00',
            'ar' => [
                'website_name' => 'سعي',
                'about_app' => 'عن التطبيق',
                'privacy' => 'سياسة الخصوصية',
                'terms_conditions' => 'الشروط والأحكام',
            ],
            'en' => [
                'website_name' => 'Saey',
                'about_app' => 'About app',
                'privacy' => 'Privacy policy',
                'terms_conditions' => 'Terms and conditions',
            ],
        ];

        $this->actingAs($this->admin, 'admin')
            ->from(route('admin.settings.edit'))
            ->put(route('admin.settings.update'), $payload)
            ->assertRedirect(route('admin.settings.edit'))
            ->assertSessionHasErrors(['logo_header']);
    }
}
