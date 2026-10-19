<?php

namespace Tests\Feature\Api;

use App\Enums\AccountTypeEnum;
use App\Enums\PaymentOperationTableEnum;
use App\Enums\PaymentStatusEnum;
use App\Enums\WalletTransactionStatusEnum;
use App\Enums\WalletTransactionTypeEnum;
use App\Models\Sai\Payment;
use App\Models\Sai\Wallet;
use App\Models\Sai\WalletTransaction;
use Database\Factories\UserFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class WalletTopUpApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_and_store_accounts_can_start_wallet_top_ups_through_the_shared_api(): void
    {
        foreach ([AccountTypeEnum::User, AccountTypeEnum::Store] as $accountType) {
            $owner = UserFactory::new()->create(['account_type' => $accountType]);

            $response = $this->actingAs($owner, 'api')
                ->postJson('/api/user/wallet', [
                    'amount' => '125.50',
                    'idempotency_key' => (string) Str::uuid(),
                ])
                ->assertOk()
                ->assertJsonPath('data.status', PaymentStatusEnum::Pending->value)
                ->assertJsonPath('data.amount', '125.50')
                ->assertJsonPath('data.currency', 'EGP');
            $this->assertStringContainsString('/pay-online/', $response->json('data.redirectUrl'));

            $this->assertDatabaseHas('wallets', [
                'user_id' => $owner->id,
                'available_balance' => '0.00',
                'pending_balance' => '125.50',
            ]);
            $this->assertDatabaseHas('wallet_transactions', [
                'wallet_id' => Wallet::query()->where('user_id', $owner->id)->value('id'),
                'status' => WalletTransactionStatusEnum::Pending->value,
                'amount' => '125.50',
            ]);
        }
    }

    public function test_top_up_is_idempotent_and_successful_payment_credits_the_wallet_once(): void
    {
        $owner = UserFactory::new()->create(['account_type' => AccountTypeEnum::User]);
        $idempotencyKey = (string) Str::uuid();
        $payload = ['amount' => '80.25', 'idempotency_key' => $idempotencyKey];

        $firstResponse = $this->actingAs($owner, 'api')->postJson('/api/user/wallet', $payload)->assertOk();
        $paymentId = $firstResponse->json('data.payment_id');

        $this->postJson('/api/user/wallet', $payload)
            ->assertOk()
            ->assertJsonPath('data.payment_id', $paymentId);

        $this->assertSame(1, Payment::query()->count());
        $this->assertSame(1, WalletTransaction::query()->count());

        $this->get('/payment-success/'.$paymentId.'/'.PaymentOperationTableEnum::WalletTopUp->value)
            ->assertOk()
            ->assertViewIs('payments.payment_success');

        $wallet = Wallet::query()->where('user_id', $owner->id)->firstOrFail();
        $this->assertSame('80.25', (string) $wallet->available_balance);
        $this->assertSame('0.00', (string) $wallet->pending_balance);

        $this->get('/payment-success/'.$paymentId.'/'.PaymentOperationTableEnum::WalletTopUp->value)->assertOk();

        $this->assertSame('80.25', (string) $wallet->fresh()->available_balance);
        $this->assertDatabaseHas('payments', [
            'id' => $paymentId,
            'status' => PaymentStatusEnum::Paid->value,
        ]);
        $this->assertDatabaseHas('wallet_transactions', [
            'wallet_id' => $wallet->id,
            'status' => WalletTransactionStatusEnum::Completed->value,
            'balance_after' => '80.25',
        ]);
    }

    public function test_failed_top_up_releases_pending_balance_and_rejects_reusing_key_for_another_amount(): void
    {
        $owner = UserFactory::new()->create(['account_type' => AccountTypeEnum::Store]);
        $idempotencyKey = (string) Str::uuid();
        $response = $this->actingAs($owner, 'api')
            ->postJson('/api/user/wallet', ['amount' => '42.00', 'idempotency_key' => $idempotencyKey])
            ->assertOk();
        $paymentId = $response->json('data.payment_id');

        $this->postJson('/api/user/wallet', ['amount' => '43.00', 'idempotency_key' => $idempotencyKey])
            ->assertUnprocessable()
            ->assertJsonPath('message', __('messages.wallet.idempotency_conflict'));

        $this->get('/payment-failed/'.$paymentId.'/'.PaymentOperationTableEnum::WalletTopUp->value)
            ->assertOk()
            ->assertViewIs('payments.payment_failed');

        $wallet = Wallet::query()->where('user_id', $owner->id)->firstOrFail();
        $this->assertSame('0.00', (string) $wallet->available_balance);
        $this->assertSame('0.00', (string) $wallet->pending_balance);
        $this->assertDatabaseHas('payments', [
            'id' => $paymentId,
            'status' => PaymentStatusEnum::Failed->value,
        ]);
    }

    public function test_balance_and_top_up_validation_require_authentication_and_return_translated_errors(): void
    {
        $this->getJson('/api/user/wallet')->assertUnauthorized();
        $this->postJson('/api/user/wallet', [])->assertUnauthorized();

        $owner = UserFactory::new()->create(['account_type' => AccountTypeEnum::User]);
        $this->actingAs($owner, 'api');

        $this->withHeader('Accept-Language', 'ar')
            ->postJson('/api/user/wallet', [
                'amount' => '0.001',
                'idempotency_key' => (string) Str::uuid(),
            ])
            ->assertUnprocessable()
            ->assertJsonPath('message', __('messages.validation.wallet_top_up.amount.decimal'));

        $this->withHeader('Accept-Language', 'en')
            ->postJson('/api/user/wallet', [
                'amount' => '10.00',
                'idempotency_key' => 'invalid-key',
            ])
            ->assertUnprocessable()
            ->assertJsonPath('message', __('messages.validation.wallet_top_up.idempotency_key.uuid'));

        $this->getJson('/api/user/wallet?limit_per_page=1')
            ->assertOk()
            ->assertJsonPath('wallet.balance', '0.00')
            ->assertJsonPath('wallet.pending_balance', '0.00')
            ->assertJsonPath('wallet.currency', 'EGP');
    }

    public function test_wallet_transactions_are_owner_scoped_and_paginated(): void
    {
        $owner = UserFactory::new()->create(['account_type' => AccountTypeEnum::User]);
        $otherOwner = UserFactory::new()->create(['account_type' => AccountTypeEnum::Store]);
        $wallet = Wallet::query()->create(['user_id' => $owner->id, 'is_active' => true]);
        $otherWallet = Wallet::query()->create(['user_id' => $otherOwner->id, 'is_active' => true]);
        $ownerTransactions = [];

        foreach ([10, 20, 30] as $index => $amount) {
            $ownerTransactions[] = WalletTransaction::query()->create([
                'public_id' => (string) Str::uuid(),
                'wallet_id' => $wallet->id,
                'type' => WalletTransactionTypeEnum::Deposit,
                'status' => WalletTransactionStatusEnum::Completed,
                'amount' => $amount,
                'balance_before' => $amount - 10,
                'balance_after' => $amount,
                'currency' => 'EGP',
                'idempotency_key' => 'owner-history-'.$index.'-'.Str::uuid(),
                'created_at' => now()->subMinutes(3 - $index),
                'updated_at' => now()->subMinutes(3 - $index),
            ]);
        }

        WalletTransaction::query()->create([
            'public_id' => (string) Str::uuid(),
            'wallet_id' => $otherWallet->id,
            'type' => WalletTransactionTypeEnum::Deposit,
            'status' => WalletTransactionStatusEnum::Completed,
            'amount' => '999.00',
            'balance_before' => '0.00',
            'balance_after' => '999.00',
            'currency' => 'EGP',
            'idempotency_key' => 'other-history-'.Str::uuid(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($owner, 'api')
            ->getJson('/api/user/wallet?page=1&limit_per_page=1')
            ->assertOk()
            ->assertJsonPath('total', 3)
            ->assertJsonPath('current_page', 1)
            ->assertJsonPath('last_page', 3)
            ->assertJsonPath('per_page', 1)
            ->assertJsonPath('data.0.id', $ownerTransactions[2]->public_id)
            ->assertJsonPath('data.0.type', WalletTransactionTypeEnum::Deposit->value)
            ->assertJsonPath('data.0.status', WalletTransactionStatusEnum::Completed->value);

        $this->getJson('/api/user/wallet?page=2&limit_per_page=1')
            ->assertOk()
            ->assertJsonPath('data.0.id', $ownerTransactions[1]->public_id);

        $this->withHeader('Accept-Language', 'en')
            ->getJson('/api/user/wallet?limit_per_page=101')
            ->assertUnprocessable()
            ->assertJsonPath('message', __('messages.validation.catalog_lookup.limit_per_page.max'));
    }

    public function test_top_up_cannot_overflow_the_wallet_decimal_balance(): void
    {
        $owner = UserFactory::new()->create(['account_type' => AccountTypeEnum::User]);
        Wallet::query()->create([
            'user_id' => $owner->id,
            'currency' => 'EGP',
            'available_balance' => '999999999999.99',
            'pending_balance' => '0.00',
            'is_active' => true,
        ]);

        $this->actingAs($owner, 'api')
            ->postJson('/api/user/wallet', [
                'amount' => '0.01',
                'idempotency_key' => (string) Str::uuid(),
            ])
            ->assertUnprocessable()
            ->assertJsonPath('message', __('messages.validation.wallet_top_up.amount.max'));

        $this->assertDatabaseCount('payments', 0);
        $this->assertDatabaseCount('wallet_transactions', 0);
    }
}
