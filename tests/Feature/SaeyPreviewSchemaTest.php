<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class SaeyPreviewSchemaTest extends TestCase
{
    use RefreshDatabase;

    public function test_preview_domains_have_database_tables(): void
    {
        $tables = [
            'social_accounts',
            'one_time_passwords',
            'user_locations',
            'notification_preferences',
            'stores',
            'store_bank_accounts',
            'store_verification_submissions',
            'store_policy_versions',
            'store_roles',
            'store_team_members',
            'products',
            'product_media',
            'inventory_movements',
            'product_favorites',
            'store_favorites',
            'product_reports',
            'wallets',
            'wallet_transactions',
            'wallet_withdrawal_requests',
            'settlement_requests',
            'coupons',
            'orders',
            'order_items',
            'order_status_histories',
            'payments',
            'coupon_redemptions',
            'order_receipts',
            'reviews',
            'return_requests',
            'return_request_media',
            'return_status_histories',
            'order_disputes',
            'order_dispute_messages',
            'refunds',
            'conversations',
            'messages',
            'ad_packages',
            'ad_package_translations',
            'ads',
            'ad_daily_metrics',
            'competitions',
            'competition_entries',
            'notifications',
            'activity_logs',
            'generated_documents',
            'account_data_requests',
            'account_deletion_requests',
        ];

        foreach ($tables as $table) {
            $this->assertTrue(Schema::hasTable($table), "Missing table: {$table}");
        }
    }

    public function test_user_and_merchant_identity_columns_exist(): void
    {
        $this->assertTrue(Schema::hasColumns('users', [
            'phone_code',
            'phone',
            'account_type',
            'status',
            'country_id',
            'governorate_id',
            'city_id',
            'avatar',
            'preferred_locale',
            'deleted_at',
        ]));

        $this->assertTrue(Schema::hasColumns('stores', [
            'owner_id',
            'category_id',
            'public_token',
            'registration_number',
            'status',
            'is_open',
        ]));
    }

    public function test_order_schema_keeps_financial_and_policy_snapshots(): void
    {
        $this->assertTrue(Schema::hasColumns('orders', [
            'user_id',
            'store_id',
            'coupon_id',
            'store_policy_version_id',
            'status',
            'payment_status',
            'subtotal',
            'discount_amount',
            'total_amount',
            'pickup_branch_snapshot',
            'policy_snapshot',
            'pickup_qr_token',
            'pickup_code_hash',
        ]));

        $this->assertTrue(Schema::hasColumns('wallet_transactions', [
            'wallet_id',
            'type',
            'status',
            'amount',
            'balance_before',
            'balance_after',
            'idempotency_key',
        ]));
    }

    public function test_dashboard_managed_domains_use_dedicated_translation_tables(): void
    {
        $translationTables = [
            'country_translations' => ['country_id', 'locale', 'name'],
            'governorate_translations' => ['governorate_id', 'locale', 'name'],
            'city_translations' => ['city_id', 'locale', 'name'],
            'category_translations' => ['category_id', 'locale', 'name'],
            'ad_package_translations' => ['ad_package_id', 'locale', 'name', 'description'],
        ];

        foreach ($translationTables as $table => $columns) {
            $this->assertTrue(Schema::hasColumns($table, $columns), "Invalid translation table: {$table}");
        }
    }

    public function test_user_entered_content_is_stored_on_its_main_table(): void
    {
        $this->assertTrue(Schema::hasColumns('users', ['name', 'avatar', 'address_line']));
        $this->assertTrue(Schema::hasColumns('stores', ['description', 'cover_image']));
        $this->assertTrue(Schema::hasColumns('products', ['name', 'description']));
        $this->assertTrue(Schema::hasColumns('store_policy_versions', [
            'pickup_instructions',
            'return_terms',
            'exchange_terms',
            'cancellation_terms',
        ]));
        $this->assertTrue(Schema::hasColumns('ads', ['title', 'action_label', 'caption']));

        $this->assertFalse(Schema::hasTable('store_translations'));
        $this->assertFalse(Schema::hasTable('store_policy_version_translations'));
        $this->assertFalse(Schema::hasTable('product_translations'));
        $this->assertFalse(Schema::hasTable('ad_translations'));
    }
}
