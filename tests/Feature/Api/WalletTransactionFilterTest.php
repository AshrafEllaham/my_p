<?php

namespace Tests\Feature\Api;

use App\Enums\WalletTransactionStatusEnum;
use App\Enums\WalletTransactionTypeEnum;
use App\Models\Sai\Wallet;
use App\Models\Sai\WalletTransaction;
use Database\Factories\UserFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class WalletTransactionFilterTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_filter_wallet_transactions_by_category(): void
    {
        $user = UserFactory::new()->create();
        $wallet = Wallet::query()->create(['user_id' => $user->id, 'is_active' => true]);

        foreach ([
            [WalletTransactionTypeEnum::Deposit, '0.00', '10.00'],
            [WalletTransactionTypeEnum::Purchase, '10.00', '5.00'],
            [WalletTransactionTypeEnum::Refund, '5.00', '7.00'],
            [WalletTransactionTypeEnum::TransferIn, '7.00', '9.00'],
            [WalletTransactionTypeEnum::TransferOut, '9.00', '8.00'],
        ] as [$type, $before, $after]) {
            WalletTransaction::query()->create([
                'public_id' => (string) Str::uuid(),
                'wallet_id' => $wallet->id,
                'type' => $type,
                'status' => WalletTransactionStatusEnum::Completed,
                'amount' => '1.00',
                'balance_before' => $before,
                'balance_after' => $after,
                'currency' => 'EGP',
                'idempotency_key' => (string) Str::uuid(),
            ]);
        }

        $this->actingAs($user, 'api')
            ->getJson('/api/user/wallet?filter=transfer&limit_per_page=10')
            ->assertOk()
            ->assertJsonPath('total', 2)
            ->assertJsonPath('data.0.type', WalletTransactionTypeEnum::TransferOut->value)
            ->assertJsonPath('data.1.type', WalletTransactionTypeEnum::TransferIn->value);
    }

    public function test_wallet_filter_validation_is_translated(): void
    {
        $user = UserFactory::new()->create();

        $this->actingAs($user, 'api')
            ->withHeader('Accept-Language', 'en')
            ->getJson('/api/user/wallet?filter=unknown')
            ->assertUnprocessable()
            ->assertJsonPath('message', __('messages.validation.wallet_transactions.filter.enum'));
    }
}
