<?php

namespace Tests\Feature\Api;

use App\Enums\AccountTypeEnum;
use App\Enums\AdActionEnum;
use App\Enums\AdPlacementEnum;
use App\Enums\AdStatusEnum;
use App\Enums\AdSubmissionActionEnum;
use App\Enums\MediaTypeEnum;
use App\Enums\PaymentMethodEnum;
use App\Enums\PaymentStatusEnum;
use App\Enums\WalletTransactionTypeEnum;
use App\Models\Sai\Ad;
use App\Models\Sai\AdDailyMetric;
use App\Models\Sai\AdPackage;
use App\Models\Sai\Category;
use App\Models\Sai\Payment;
use App\Models\Sai\Settings;
use App\Models\Sai\Store;
use App\Models\Sai\Wallet;
use App\Models\Sai\WalletTransaction;
use Database\Factories\UserFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class StoreAdApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    public function test_store_can_create_list_edit_toggle_and_delete_its_ads(): void
    {
        $store = UserFactory::new()->create(['account_type' => AccountTypeEnum::Store]);
        $otherStore = UserFactory::new()->create(['account_type' => AccountTypeEnum::Store]);
        $category = Category::factory()->create();
        Store::query()->create(['owner_id' => $store->id, 'category_id' => $category->id]);
        Store::query()->create(['owner_id' => $otherStore->id, 'category_id' => $category->id]);
        $package = AdPackage::query()->create([
            'code' => 'weekly', 'duration_days' => 7, 'price' => 350, 'currency' => 'EGP', 'is_active' => true,
        ]);
        $token = $this->tokenFor($store);

        $this->withToken($token)->getJson('/api/store/ad-packages')
            ->assertOk()->assertJsonPath('data.0.id', $package->id);

        $created = $this->withToken($token)->post('/api/store/ads', [
            'ad_package_id' => $package->id,
            'submission_action' => AdSubmissionActionEnum::SaveAsDraft->value,
            'title' => 'Summer offer',
            'placement' => AdPlacementEnum::Home->value,
            'action' => AdActionEnum::ViewProduct->value,
            'media_type' => MediaTypeEnum::Image->value,
            'media' => UploadedFile::fake()->image('offer.jpg'),
        ], ['Accept' => 'application/json'])->assertOk()
            ->assertJsonPath('message', __('messages.ads.created'))
            ->assertJsonPath('data.status', AdStatusEnum::Draft->value)
            ->assertJsonPath('data.cost', '350.00');

        $adId = $created->json('data.id');
        Ad::query()->whereKey($adId)->update(['status' => AdStatusEnum::Active->value]);

        $this->withToken($token)->patchJson("/api/store/ads/{$adId}", ['title' => 'Updated offer'])
            ->assertOk()->assertJsonPath('data.title', 'Updated offer');

        $this->withToken($token)->patchJson("/api/store/ads/{$adId}/toggle")
            ->assertOk()->assertJsonPath('data.status', AdStatusEnum::Paused->value);

        $this->withToken($token)->getJson('/api/store/ads?status=paused')
            ->assertOk()->assertJsonPath('data.0.id', $adId);

        $this->withToken($token)->deleteJson("/api/store/ads/{$adId}")
            ->assertOk()->assertJsonPath('message', __('messages.ads.deleted'));
        $this->assertSoftDeleted('ads', ['id' => $adId]);

        $this->withToken($this->tokenFor($otherStore))->getJson('/api/store/ads')
            ->assertOk()->assertJsonCount(0, 'data');
    }

    public function test_store_ad_list_returns_unfiltered_performance_summary(): void
    {
        $owner = UserFactory::new()->create(['account_type' => AccountTypeEnum::Store]);
        $otherOwner = UserFactory::new()->create(['account_type' => AccountTypeEnum::Store]);
        $category = Category::factory()->create();
        $store = Store::query()->create(['owner_id' => $owner->id, 'category_id' => $category->id]);
        $otherStore = Store::query()->create(['owner_id' => $otherOwner->id, 'category_id' => $category->id]);
        $package = AdPackage::query()->create([
            'code' => 'summary-package', 'duration_days' => 7, 'price' => 100, 'currency' => 'EGP', 'is_active' => true,
        ]);

        $activeAd = $this->createSummaryAd($store->id, $package->id, AdStatusEnum::Active);
        $draftAd = $this->createSummaryAd($store->id, $package->id, AdStatusEnum::Draft);
        $deletedAd = $this->createSummaryAd($store->id, $package->id, AdStatusEnum::Active);
        $otherStoreAd = $this->createSummaryAd($otherStore->id, $package->id, AdStatusEnum::Active);
        $deletedAd->delete();

        AdDailyMetric::query()->create([
            'ad_id' => $activeAd->id,
            'metric_date' => now()->toDateString(),
            'impressions' => 120,
            'chats_started' => 4,
        ]);
        AdDailyMetric::query()->create([
            'ad_id' => $draftAd->id,
            'metric_date' => now()->subMonth()->toDateString(),
            'impressions' => 30,
            'chats_started' => 2,
        ]);
        AdDailyMetric::query()->create([
            'ad_id' => $deletedAd->id,
            'metric_date' => now()->toDateString(),
            'impressions' => 500,
            'chats_started' => 10,
        ]);
        AdDailyMetric::query()->create([
            'ad_id' => $otherStoreAd->id,
            'metric_date' => now()->toDateString(),
            'impressions' => 900,
            'chats_started' => 20,
        ]);

        $this->withToken($this->tokenFor($owner))->getJson('/api/store/ads?status=draft')
            ->assertOk()
            ->assertJsonPath('data.0.id', $draftAd->id)
            ->assertJsonPath('total_impressions_count', 150)
            ->assertJsonPath('total_impressions_this_month_count', 120)
            ->assertJsonPath('total_new_customers_count', 6)
            ->assertJsonPath('total_active_campaigns_count', 1);
    }

    public function test_home_and_category_ads_use_their_configured_settings_prices(): void
    {
        $owner = UserFactory::new()->create(['account_type' => AccountTypeEnum::Store]);
        $category = Category::factory()->create();
        Store::query()->create(['owner_id' => $owner->id, 'category_id' => $category->id]);
        $package = AdPackage::query()->create([
            'code' => 'placement-prices', 'duration_days' => 7, 'price' => 350, 'currency' => 'EGP', 'is_active' => true,
        ]);
        Settings::query()->create([
            'ad_home_price' => '125.50',
            'ad_category_price' => '75.00',
        ]);
        $token = $this->tokenFor($owner);

        foreach ([
            [AdPlacementEnum::Home, '125.50'],
            [AdPlacementEnum::Category, '75.00'],
        ] as [$placement, $expectedPrice]) {
            $this->withToken($token)->post('/api/store/ads', [
                'ad_package_id' => $package->id,
                'submission_action' => AdSubmissionActionEnum::SaveAsDraft->value,
                'title' => 'Placement price check',
                'placement' => $placement->value,
                'category_id' => $placement === AdPlacementEnum::Category ? $category->id : null,
                'action' => AdActionEnum::ViewStore->value,
                'media_type' => MediaTypeEnum::Image->value,
                'media' => UploadedFile::fake()->image('placement.jpg'),
            ], ['Accept' => 'application/json'])
                ->assertOk()
                ->assertJsonPath('data.cost', $expectedPrice);
        }
    }

    public function test_ad_validation_errors_are_localized_and_store_access_is_required(): void
    {
        $this->postJson('/api/store/ads')->assertUnauthorized();
        $this->getJson('/api/store/ads/wallet-balance')->assertUnauthorized();
        $this->postJson('/api/store/ads/1/submit')->assertUnauthorized();

        $store = UserFactory::new()->create(['account_type' => AccountTypeEnum::Store]);
        $category = Category::factory()->create();
        Store::query()->create(['owner_id' => $store->id, 'category_id' => $category->id]);
        $token = $this->tokenFor($store);

        $this->withHeader('Accept-Language', 'en')->withToken($token)
            ->postJson('/api/store/ads', [])
            ->assertUnprocessable()
            ->assertJsonPath('message', 'Choose an active ad package.');

        $this->withHeader('Accept-Language', 'ar')->withToken($token)
            ->postJson('/api/store/ads', [])
            ->assertUnprocessable()
            ->assertJsonPath('message', 'اختر باقة إعلان نشطة.');

        $package = AdPackage::query()->create([
            'code' => 'missing-submit-action', 'duration_days' => 7, 'price' => 350, 'currency' => 'EGP', 'is_active' => true,
        ]);
        $validAd = [
            'ad_package_id' => $package->id,
            'title' => 'Missing action',
            'placement' => AdPlacementEnum::Home->value,
            'action' => AdActionEnum::ViewProduct->value,
            'media_type' => MediaTypeEnum::Image->value,
            'media' => UploadedFile::fake()->image('missing-action.jpg'),
        ];
        $this->withHeader('Accept-Language', 'en')->withToken($token)
            ->post('/api/store/ads', $validAd, ['Accept' => 'application/json'])
            ->assertUnprocessable()
            ->assertJsonPath('message', 'Choose whether to save the ad as a draft or submit it for review.');
        $validAd['submission_action'] = AdSubmissionActionEnum::SubmitForReview->value;
        $this->withHeader('Accept-Language', 'ar')->withToken($token)
            ->post('/api/store/ads', $validAd, ['Accept' => 'application/json'])
            ->assertUnprocessable()
            ->assertJsonPath('message', 'معرّف محاولة الإرسال مطلوب عند اختيار الإرسال للمراجعة.');

        $validAd['idempotency_key'] = (string) Str::uuid();
        $this->withHeader('Accept-Language', 'en')->withToken($token)
            ->post('/api/store/ads', $validAd, ['Accept' => 'application/json'])
            ->assertUnprocessable()
            ->assertJsonPath('message', 'Choose a payment method for the ad.');

        $user = UserFactory::new()->create(['account_type' => AccountTypeEnum::User]);
        $this->withToken($this->tokenFor($user))->getJson('/api/store/ads')
            ->assertUnprocessable()
            ->assertJsonPath('message', __('messages.profile.store_type_required'));
    }

    public function test_store_can_pay_for_a_draft_ad_and_submit_it_for_review_once(): void
    {
        $owner = UserFactory::new()->create(['account_type' => AccountTypeEnum::Store]);
        $category = Category::factory()->create();
        $store = Store::query()->create(['owner_id' => $owner->id, 'category_id' => $category->id]);
        $package = AdPackage::query()->create([
            'code' => 'paid-submit', 'duration_days' => 7, 'price' => 350, 'currency' => 'EGP', 'is_active' => true,
        ]);
        $wallet = Wallet::query()->create([
            'user_id' => $owner->id, 'currency' => 'EGP', 'available_balance' => 500, 'pending_balance' => 0, 'is_active' => true,
        ]);
        $token = $this->tokenFor($owner);

        $this->withToken($token)->getJson('/api/store/ads/wallet-balance')
            ->assertOk()
            ->assertJsonPath('data.balance', '500.00')
            ->assertJsonPath('data.currency', 'EGP');

        $created = $this->withToken($token)->post('/api/store/ads', [
            'ad_package_id' => $package->id,
            'submission_action' => AdSubmissionActionEnum::SaveAsDraft->value,
            'title' => 'Paid campaign',
            'placement' => AdPlacementEnum::Home->value,
            'action' => AdActionEnum::ViewProduct->value,
            'media_type' => MediaTypeEnum::Image->value,
            'media' => UploadedFile::fake()->image('paid.jpg'),
        ], ['Accept' => 'application/json'])->assertOk()
            ->assertJsonPath('data.status', AdStatusEnum::Draft->value);

        $adId = $created->json('data.id');
        $this->withToken($token)->postJson("/api/store/ads/{$adId}/submit", ['payment_method' => PaymentMethodEnum::Wallet->value])
            ->assertOk()
            ->assertJsonPath('message', __('messages.ads.submitted'))
            ->assertJsonPath('data.status', AdStatusEnum::PendingReview->value);

        $this->withToken($token)->postJson("/api/store/ads/{$adId}/submit", ['payment_method' => PaymentMethodEnum::Wallet->value])
            ->assertOk()
            ->assertJsonPath('data.status', AdStatusEnum::PendingReview->value);

        $this->assertDatabaseHas('wallets', ['id' => $wallet->id, 'available_balance' => '150.00']);
        $this->assertSame(1, WalletTransaction::query()->where('wallet_id', $wallet->id)
            ->where('type', WalletTransactionTypeEnum::AdPayment->value)->count());
        $this->assertDatabaseHas('ads', [
            'id' => $adId,
            'store_id' => $store->id,
            'status' => AdStatusEnum::PendingReview->value,
        ]);
    }

    public function test_insufficient_wallet_keeps_ad_as_draft_and_free_storefront_ad_needs_no_wallet(): void
    {
        $owner = UserFactory::new()->create(['account_type' => AccountTypeEnum::Store]);
        $category = Category::factory()->create();
        Store::query()->create(['owner_id' => $owner->id, 'category_id' => $category->id]);
        $package = AdPackage::query()->create([
            'code' => 'wallet-short', 'duration_days' => 7, 'price' => 350, 'currency' => 'EGP', 'is_active' => true,
        ]);
        Wallet::query()->create([
            'user_id' => $owner->id, 'currency' => 'EGP', 'available_balance' => 100, 'pending_balance' => 0, 'is_active' => true,
        ]);
        $token = $this->tokenFor($owner);

        $created = $this->withToken($token)->post('/api/store/ads', [
            'ad_package_id' => $package->id,
            'submission_action' => AdSubmissionActionEnum::SaveAsDraft->value,
            'title' => 'Needs payment',
            'placement' => AdPlacementEnum::Home->value,
            'action' => AdActionEnum::ViewProduct->value,
            'media_type' => MediaTypeEnum::Image->value,
            'media' => UploadedFile::fake()->image('needs-payment.jpg'),
        ], ['Accept' => 'application/json'])->assertOk();
        $adId = $created->json('data.id');

        $this->withHeader('Accept-Language', 'en')->withToken($token)
            ->postJson("/api/store/ads/{$adId}/submit", ['payment_method' => PaymentMethodEnum::Wallet->value])
            ->assertUnprocessable()
            ->assertJsonPath('message', __('messages.ads.wallet_insufficient'));
        $this->assertDatabaseHas('ads', ['id' => $adId, 'status' => AdStatusEnum::Draft->value]);
        $this->assertDatabaseHas('wallets', ['user_id' => $owner->id, 'available_balance' => '100.00']);
        Wallet::query()->where('user_id', $owner->id)->delete();

        $freeAd = $this->withToken($token)->post('/api/store/ads', [
            'ad_package_id' => $package->id,
            'submission_action' => AdSubmissionActionEnum::SubmitForReview->value,
            'idempotency_key' => (string) Str::uuid(),
            'payment_method' => PaymentMethodEnum::Wallet->value,
            'title' => 'Storefront campaign',
            'placement' => AdPlacementEnum::Storefront->value,
            'action' => AdActionEnum::ViewStore->value,
            'media_type' => MediaTypeEnum::Image->value,
            'media' => UploadedFile::fake()->image('storefront.jpg'),
        ], ['Accept' => 'application/json'])->assertOk()
            ->assertJsonPath('data.cost', '0.00')
            ->assertJsonPath('data.status', AdStatusEnum::PendingReview->value);
        $this->assertDatabaseCount('wallet_transactions', 0);
    }

    public function test_store_can_create_pay_and_submit_an_ad_in_one_request_idempotently(): void
    {
        $owner = UserFactory::new()->create(['account_type' => AccountTypeEnum::Store]);
        $category = Category::factory()->create();
        $store = Store::query()->create(['owner_id' => $owner->id, 'category_id' => $category->id]);
        $package = AdPackage::query()->create([
            'code' => 'direct-submit', 'duration_days' => 7, 'price' => 350, 'currency' => 'EGP', 'is_active' => true,
        ]);
        $wallet = Wallet::query()->create([
            'user_id' => $owner->id, 'currency' => 'EGP', 'available_balance' => 500, 'pending_balance' => 0, 'is_active' => true,
        ]);
        $token = $this->tokenFor($owner);
        $creationKey = (string) Str::uuid();
        $payload = [
            'ad_package_id' => $package->id,
            'submission_action' => AdSubmissionActionEnum::SubmitForReview->value,
            'idempotency_key' => $creationKey,
            'payment_method' => PaymentMethodEnum::Wallet->value,
            'title' => 'Submit immediately',
            'placement' => AdPlacementEnum::Home->value,
            'action' => AdActionEnum::ViewProduct->value,
            'media_type' => MediaTypeEnum::Image->value,
            'media' => UploadedFile::fake()->image('direct-submit.jpg'),
        ];

        $created = $this->withToken($token)->post('/api/store/ads', $payload, ['Accept' => 'application/json'])
            ->assertOk()
            ->assertJsonPath('message', __('messages.ads.submitted'))
            ->assertJsonPath('data.status', AdStatusEnum::PendingReview->value)
            ->assertJsonPath('data.cost', '350.00');
        $adId = $created->json('data.id');

        $this->withToken($token)->post('/api/store/ads', $payload, ['Accept' => 'application/json'])
            ->assertOk()
            ->assertJsonPath('data.id', $adId)
            ->assertJsonPath('data.status', AdStatusEnum::PendingReview->value);

        $this->assertDatabaseHas('ads', ['id' => $adId, 'store_id' => $store->id]);
        $this->assertDatabaseHas('wallets', ['id' => $wallet->id, 'available_balance' => '150.00']);
        $this->assertSame(1, WalletTransaction::query()->where('wallet_id', $wallet->id)
            ->where('type', WalletTransactionTypeEnum::AdPayment->value)->count());
    }

    public function test_direct_submission_with_insufficient_balance_does_not_create_an_ad(): void
    {
        $owner = UserFactory::new()->create(['account_type' => AccountTypeEnum::Store]);
        $category = Category::factory()->create();
        Store::query()->create(['owner_id' => $owner->id, 'category_id' => $category->id]);
        $package = AdPackage::query()->create([
            'code' => 'direct-wallet-short', 'duration_days' => 7, 'price' => 350, 'currency' => 'EGP', 'is_active' => true,
        ]);
        Wallet::query()->create([
            'user_id' => $owner->id, 'currency' => 'EGP', 'available_balance' => 100, 'pending_balance' => 0, 'is_active' => true,
        ]);
        $token = $this->tokenFor($owner);

        $this->withHeader('Accept-Language', 'en')->withToken($token)->post('/api/store/ads', [
            'ad_package_id' => $package->id,
            'submission_action' => AdSubmissionActionEnum::SubmitForReview->value,
            'idempotency_key' => (string) Str::uuid(),
            'payment_method' => PaymentMethodEnum::Wallet->value,
            'title' => 'Insufficient balance',
            'placement' => AdPlacementEnum::Home->value,
            'action' => AdActionEnum::ViewProduct->value,
            'media_type' => MediaTypeEnum::Image->value,
            'media' => UploadedFile::fake()->image('wallet-short.jpg'),
        ], ['Accept' => 'application/json'])->assertUnprocessable()
            ->assertJsonPath('message', __('messages.ads.wallet_insufficient'));

        $this->assertDatabaseCount('ads', 0);
        $this->assertDatabaseHas('wallets', ['user_id' => $owner->id, 'available_balance' => '100.00']);
        $this->assertDatabaseCount('wallet_transactions', 0);
    }

    public function test_direct_online_submission_creates_pending_payment_and_returns_payment_links_idempotently(): void
    {
        $owner = UserFactory::new()->create(['account_type' => AccountTypeEnum::Store]);
        $category = Category::factory()->create();
        $store = Store::query()->create(['owner_id' => $owner->id, 'category_id' => $category->id]);
        $package = AdPackage::query()->create([
            'code' => 'online-submit', 'duration_days' => 7, 'price' => 350, 'currency' => 'EGP', 'is_active' => true,
        ]);
        $wallet = Wallet::query()->create([
            'user_id' => $owner->id, 'currency' => 'EGP', 'available_balance' => 10, 'pending_balance' => 0, 'is_active' => true,
        ]);
        $token = $this->tokenFor($owner);
        $creationKey = (string) Str::uuid();
        $payload = [
            'ad_package_id' => $package->id,
            'submission_action' => AdSubmissionActionEnum::SubmitForReview->value,
            'idempotency_key' => $creationKey,
            'payment_method' => PaymentMethodEnum::Online->value,
            'title' => 'Online campaign',
            'placement' => AdPlacementEnum::Home->value,
            'action' => AdActionEnum::ViewProduct->value,
            'media_type' => MediaTypeEnum::Image->value,
            'media' => UploadedFile::fake()->image('online-ad.jpg'),
        ];

        $response = $this->withToken($token)->post('/api/store/ads', $payload, ['Accept' => 'application/json'])
            ->assertOk()
            ->assertJsonPath('message', __('messages.ads.payment_required'))
            ->assertJsonMissingPath('data.id')
            ->assertJsonMissingPath('data.status');

        $payment = Payment::query()->where('idempotency_key', 'ad-creation-payment-'.$creationKey)->firstOrFail();

        $response->assertJsonPath('data.redirectUrl', route('web.pay_online', ['id' => $payment->id, 'type' => 'pay_trip']))
            ->assertJsonPath('data.paymentSuccess', route('payment.success', ['id' => $payment->id, 'type' => 'pay_trip']))
            ->assertJsonPath('data.paymentaFiled', route('payment.failed', ['id' => $payment->id, 'type' => 'pay_trip']))
            ->assertJsonCount(3, 'data');

        $this->withToken($token)->post('/api/store/ads', $payload, ['Accept' => 'application/json'])
            ->assertOk()
            ->assertJsonPath('data.redirectUrl', route('web.pay_online', ['id' => $payment->id, 'type' => 'pay_trip']));

        $this->assertSame(1, Payment::query()->where('idempotency_key', 'ad-creation-payment-'.$creationKey)->count());
        $this->assertSame(PaymentStatusEnum::Pending, $payment->status);
        $this->assertSame(PaymentMethodEnum::Online, $payment->method);
        $this->assertSame('350.00', $payment->amount);
        $this->assertNull($payment->ad_id);
        $this->assertDatabaseMissing('ads', ['store_id' => $store->id, 'creation_idempotency_key' => $creationKey]);
        $this->assertDatabaseHas('wallets', ['id' => $wallet->id, 'available_balance' => '10.00']);
        $this->assertDatabaseCount('wallet_transactions', 0);

        $this->get(route('payment.success', ['id' => $payment->id, 'type' => 'pay_trip']))->assertOk();
        $ad = Ad::query()->where('creation_idempotency_key', $creationKey)->firstOrFail();
        $payment->refresh();

        $this->assertSame(AdStatusEnum::PendingReview, $ad->status);
        $this->assertSame(PaymentStatusEnum::Paid, $payment->status);
        $this->assertSame($ad->id, $payment->ad_id);
        $this->assertNull($payment->metadata);
    }

    public function test_existing_draft_can_be_submitted_with_online_payment_and_pending_attempt_locks_edits(): void
    {
        $owner = UserFactory::new()->create(['account_type' => AccountTypeEnum::Store]);
        $category = Category::factory()->create();
        $store = Store::query()->create(['owner_id' => $owner->id, 'category_id' => $category->id]);
        $package = AdPackage::query()->create([
            'code' => 'online-draft-submit', 'duration_days' => 7, 'price' => 275, 'currency' => 'EGP', 'is_active' => true,
        ]);
        $token = $this->tokenFor($owner);
        $draft = $this->withToken($token)->post('/api/store/ads', [
            'ad_package_id' => $package->id,
            'submission_action' => AdSubmissionActionEnum::SaveAsDraft->value,
            'title' => 'Draft for online payment',
            'placement' => AdPlacementEnum::Home->value,
            'action' => AdActionEnum::ViewProduct->value,
            'media_type' => MediaTypeEnum::Image->value,
            'media' => UploadedFile::fake()->image('online-draft.jpg'),
        ], ['Accept' => 'application/json'])->assertOk();
        $adId = $draft->json('data.id');

        $submitted = $this->withToken($token)->postJson("/api/store/ads/{$adId}/submit", [
            'payment_method' => PaymentMethodEnum::Online->value,
        ])->assertOk()->assertJsonMissingPath('data.id');
        $payment = Payment::query()->where('ad_id', $adId)->firstOrFail();

        $submitted->assertJsonPath('data.redirectUrl', route('web.pay_online', ['id' => $payment->id, 'type' => 'pay_trip']))
            ->assertJsonPath('data.paymentSuccess', route('payment.success', ['id' => $payment->id, 'type' => 'pay_trip']))
            ->assertJsonPath('data.paymentaFiled', route('payment.failed', ['id' => $payment->id, 'type' => 'pay_trip']))
            ->assertJsonCount(3, 'data');

        $this->withToken($token)->patchJson("/api/store/ads/{$adId}", ['title' => 'Changed while paying'])
            ->assertUnprocessable();
        $this->assertDatabaseHas('ads', ['id' => $adId, 'store_id' => $store->id, 'title' => 'Draft for online payment']);

        $mediaPath = Ad::query()->findOrFail($adId)->media_path;
        $this->get(route('payment.failed', ['id' => $payment->id, 'type' => 'pay_trip']))->assertOk();

        $payment->refresh();
        $this->assertSame(PaymentStatusEnum::Failed, $payment->status);
        $this->assertNull($payment->metadata);
        $this->assertSoftDeleted('ads', ['id' => $adId]);
        Storage::disk('public')->assertMissing($mediaPath);
        $this->withToken($token)->getJson("/api/store/ads/{$adId}")->assertNotFound();
    }

    public function test_failed_direct_online_payment_does_not_leave_an_ad_or_uploaded_media(): void
    {
        $owner = UserFactory::new()->create(['account_type' => AccountTypeEnum::Store]);
        $category = Category::factory()->create();
        Store::query()->create(['owner_id' => $owner->id, 'category_id' => $category->id]);
        $package = AdPackage::query()->create([
            'code' => 'failed-online', 'duration_days' => 7, 'price' => 350, 'currency' => 'EGP', 'is_active' => true,
        ]);
        $token = $this->tokenFor($owner);
        $creationKey = (string) Str::uuid();
        $this->withToken($token)->post('/api/store/ads', [
            'ad_package_id' => $package->id,
            'submission_action' => AdSubmissionActionEnum::SubmitForReview->value,
            'idempotency_key' => $creationKey,
            'payment_method' => PaymentMethodEnum::Online->value,
            'title' => 'Failed online campaign',
            'placement' => AdPlacementEnum::Home->value,
            'action' => AdActionEnum::ViewProduct->value,
            'media_type' => MediaTypeEnum::Image->value,
            'media' => UploadedFile::fake()->image('failed-online.jpg'),
        ], ['Accept' => 'application/json'])->assertOk();

        $payment = Payment::query()->where('idempotency_key', 'ad-creation-payment-'.$creationKey)->firstOrFail();
        $mediaPath = $payment->metadata['ad_attributes']['media_path'];
        $this->assertDatabaseCount('ads', 0);
        Storage::disk('public')->assertExists($mediaPath);

        $this->get(route('payment.failed', ['id' => $payment->id, 'type' => 'pay_trip']))->assertOk();

        $payment->refresh();
        $this->assertSame(PaymentStatusEnum::Failed, $payment->status);
        $this->assertNull($payment->metadata);
        $this->assertDatabaseCount('ads', 0);
        Storage::disk('public')->assertMissing($mediaPath);
    }

    public function test_store_can_get_ad_details_and_only_seven_days_of_metrics(): void
    {
        $owner = UserFactory::new()->create(['account_type' => AccountTypeEnum::Store]);
        $otherOwner = UserFactory::new()->create(['account_type' => AccountTypeEnum::Store]);
        $category = Category::factory()->create();
        $store = Store::query()->create(['owner_id' => $owner->id, 'category_id' => $category->id]);
        Store::query()->create(['owner_id' => $otherOwner->id, 'category_id' => $category->id]);
        $package = AdPackage::query()->create([
            'code' => 'weekly-details', 'duration_days' => 7, 'price' => 350, 'currency' => 'EGP', 'is_active' => true,
        ]);
        $ad = Ad::query()->create([
            'public_id' => (string) Str::uuid(),
            'store_id' => $store->id,
            'ad_package_id' => $package->id,
            'title' => 'Details campaign',
            'placement' => AdPlacementEnum::Home,
            'action' => AdActionEnum::ViewStore,
            'media_type' => MediaTypeEnum::Image,
            'media_path' => 'ads/details.jpg',
            'status' => AdStatusEnum::Active,
            'cost' => 350,
            'currency' => 'EGP',
        ]);
        AdDailyMetric::query()->create([
            'ad_id' => $ad->id,
            'metric_date' => now()->subDays(2)->toDateString(),
            'impressions' => 120,
            'clicks' => 15,
            'chats_started' => 4,
            'orders_attributed' => 2,
            'revenue_attributed' => 500,
        ]);
        AdDailyMetric::query()->create([
            'ad_id' => $ad->id,
            'metric_date' => now()->subDays(8)->toDateString(),
            'impressions' => 900,
            'clicks' => 90,
            'chats_started' => 20,
            'orders_attributed' => 10,
            'revenue_attributed' => 2400,
        ]);

        $this->withToken($this->tokenFor($owner))->getJson("/api/store/ads/{$ad->id}")
            ->assertOk()
            ->assertJsonPath('message', __('messages.ads.retrieved'))
            ->assertJsonPath('data.title', 'Details campaign')
            ->assertJsonPath('data.package.duration_days', 7)
            ->assertJsonPath('data.metrics.impressions', 120)
            ->assertJsonPath('data.metrics.clicks', 15)
            ->assertJsonCount(1, 'data.daily_metrics');

        $this->withToken($this->tokenFor($otherOwner))->getJson("/api/store/ads/{$ad->id}")
            ->assertNotFound();
    }

    public function test_store_cannot_edit_or_delete_another_stores_ad(): void
    {
        $owner = UserFactory::new()->create(['account_type' => AccountTypeEnum::Store]);
        $otherStoreOwner = UserFactory::new()->create(['account_type' => AccountTypeEnum::Store]);
        $category = Category::factory()->create();
        $ownerStore = Store::query()->create(['owner_id' => $owner->id, 'category_id' => $category->id]);
        Store::query()->create(['owner_id' => $otherStoreOwner->id, 'category_id' => $category->id]);
        $package = AdPackage::query()->create([
            'code' => 'monthly', 'duration_days' => 30, 'price' => 900, 'currency' => 'EGP', 'is_active' => true,
        ]);
        $ad = Ad::query()->create([
            'public_id' => (string) Str::uuid(),
            'store_id' => $ownerStore->id,
            'ad_package_id' => $package->id,
            'title' => 'Private campaign',
            'placement' => AdPlacementEnum::Home,
            'action' => AdActionEnum::ViewStore,
            'media_type' => MediaTypeEnum::Image,
            'media_path' => 'ads/private.jpg',
            'status' => AdStatusEnum::Draft,
            'cost' => 900,
            'currency' => 'EGP',
        ]);

        $token = $this->tokenFor($otherStoreOwner);
        $this->withToken($token)->patchJson("/api/store/ads/{$ad->id}", ['title' => 'Changed'])
            ->assertNotFound();
        $this->withToken($token)->deleteJson("/api/store/ads/{$ad->id}")
            ->assertNotFound();
        $this->withToken($token)->postJson("/api/store/ads/{$ad->id}/submit", ['payment_method' => PaymentMethodEnum::Wallet->value])
            ->assertNotFound();
        $this->assertDatabaseHas('ads', ['id' => $ad->id, 'title' => 'Private campaign', 'deleted_at' => null]);
    }

    public function test_ad_preview_uses_store_ad_endpoints_for_campaign_management(): void
    {
        $adsScreen = file_get_contents(base_path('Figma/screens/19-distributor-ads.html'));
        $editorScreen = file_get_contents(base_path('Figma/screens/19-add-ad.html'));
        $detailsScreen = file_get_contents(base_path('Figma/screens/distributor-ad-details.html'));

        $this->assertIsString($adsScreen);
        $this->assertIsString($editorScreen);
        $this->assertIsString($detailsScreen);
        $this->assertStringContainsString('/ads?${query}', $adsScreen);
        $this->assertStringContainsString('updateAdsSummary(result)', $adsScreen);
        $this->assertStringContainsString('summary.total_impressions_count', $adsScreen);
        $this->assertStringContainsString('summary.total_impressions_this_month_count', $adsScreen);
        $this->assertStringContainsString('summary.total_new_customers_count', $adsScreen);
        $this->assertStringContainsString('summary.total_active_campaigns_count', $adsScreen);
        $this->assertStringContainsString('/toggle', $adsScreen);
        $this->assertStringContainsString("'/ad-packages'", $editorScreen);
        $this->assertStringContainsString("apiRequest('/settings', {}, generalApiBase)", $editorScreen);
        $this->assertStringContainsString('adPricing = {', $editorScreen);
        $this->assertStringContainsString("'/ads/wallet-balance'", $editorScreen);
        $this->assertStringContainsString("'/ads'", $editorScreen);
        $this->assertStringContainsString("body.append('submission_action', submissionAction)", $editorScreen);
        $this->assertStringContainsString("'submit_for_review'", $editorScreen);
        $this->assertStringContainsString('/ads/${ad.id}/submit', file_get_contents(base_path('Figma/screens/19-ad-review.html')));
        $this->assertStringContainsString('`/ads/${editId}`', $editorScreen);
        $this->assertStringContainsString('/ads/${encodeURIComponent(selectedAdId)}', $detailsScreen);
        $this->assertStringContainsString('ad.daily_metrics', $detailsScreen);
    }

    private function createSummaryAd(int $storeId, int $packageId, AdStatusEnum $status): Ad
    {
        return Ad::query()->create([
            'public_id' => (string) Str::uuid(),
            'store_id' => $storeId,
            'ad_package_id' => $packageId,
            'title' => 'Summary campaign',
            'placement' => AdPlacementEnum::Home,
            'action' => AdActionEnum::ViewStore,
            'media_type' => MediaTypeEnum::Image,
            'media_path' => 'ads/summary.jpg',
            'status' => $status,
            'cost' => 100,
            'currency' => 'EGP',
        ]);
    }

    private function tokenFor(object $user): string
    {
        return (string) auth('api')->login($user);
    }
}
