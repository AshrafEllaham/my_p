<?php

namespace Tests\Feature\Api;

use App\Enums\AccountTypeEnum;
use App\Enums\UserStatusEnum;
use App\Enums\WalletTransactionStatusEnum;
use App\Enums\WalletTransactionTypeEnum;
use App\Models\Sai\User;
use App\Models\Sai\Wallet;
use App\Models\Sai\WalletTransaction;
use Database\Factories\UserFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class WalletTransferApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_transfer_balance_to_another_active_account(): void
    {
        $sender = $this->makeActiveUser(['available_balance' => '250.00', 'pending_balance' => '7.00']);
        $recipient = $this->makeActiveUser(
            ['available_balance' => '30.00', 'pending_balance' => '11.00'],
            ['account_type' => AccountTypeEnum::Store],
        );

        $this->actingAs($sender, 'api')
            ->postJson('/api/user/wallet/transfers', [
                'recipient' => $recipient->email,
                'amount' => '75.50',
                'idempotency_key' => (string) Str::uuid(),
            ])
            ->assertOk()
            ->assertJsonPath('data.recipient.name', $recipient->name)
            ->assertJsonPath('data.amount', '75.50')
            ->assertJsonPath('data.currency', 'EGP')
            ->assertJsonPath('data.status', WalletTransactionStatusEnum::Completed->value)
            ->assertJsonPath('data.balance_before', '250.00')
            ->assertJsonPath('data.balance_after', '174.50');

        $senderWallet = Wallet::query()->where('user_id', $sender->id)->firstOrFail();
        $recipientWallet = Wallet::query()->where('user_id', $recipient->id)->firstOrFail();

        $this->assertSame('174.50', (string) $senderWallet->available_balance);
        $this->assertSame('7.00', (string) $senderWallet->pending_balance);
        $this->assertSame('105.50', (string) $recipientWallet->available_balance);
        $this->assertSame('11.00', (string) $recipientWallet->pending_balance);

        $this->assertDatabaseHas('wallet_transactions', [
            'wallet_id' => $senderWallet->id,
            'counterparty_wallet_id' => $recipientWallet->id,
            'type' => WalletTransactionTypeEnum::TransferOut->value,
            'status' => WalletTransactionStatusEnum::Completed->value,
            'amount' => '75.50',
            'balance_before' => '250.00',
            'balance_after' => '174.50',
        ]);
        $this->assertDatabaseHas('wallet_transactions', [
            'wallet_id' => $recipientWallet->id,
            'counterparty_wallet_id' => $senderWallet->id,
            'type' => WalletTransactionTypeEnum::TransferIn->value,
            'status' => WalletTransactionStatusEnum::Completed->value,
            'amount' => '75.50',
            'balance_before' => '30.00',
            'balance_after' => '105.50',
        ]);
    }

    public function test_repeating_transfer_request_is_idempotent_and_conflicting_payload_is_rejected(): void
    {
        $sender = $this->makeActiveUser(['available_balance' => '100.00']);
        $recipient = $this->makeActiveUser();
        $payload = [
            'recipient' => $recipient->email,
            'amount' => '25.00',
            'idempotency_key' => (string) Str::uuid(),
        ];

        $firstResponse = $this->actingAs($sender, 'api')
            ->postJson('/api/user/wallet/transfers', $payload)
            ->assertOk();

        $this->postJson('/api/user/wallet/transfers', $payload)
            ->assertOk()
            ->assertJsonPath('data.id', $firstResponse->json('data.id'));

        $this->assertSame(2, WalletTransaction::query()->count());
        $this->assertSame('75.00', (string) Wallet::query()->where('user_id', $sender->id)->value('available_balance'));

        $this->postJson('/api/user/wallet/transfers', [...$payload, 'amount' => '30.00'])
            ->assertUnprocessable()
            ->assertJsonPath('message', __('messages.wallet.transfer_idempotency_conflict'));

        $this->assertSame(2, WalletTransaction::query()->count());
        $this->assertSame('75.00', (string) Wallet::query()->where('user_id', $sender->id)->value('available_balance'));
    }

    public function test_transfer_rejects_self_unknown_or_inactive_recipient_and_insufficient_balance(): void
    {
        $sender = $this->makeActiveUser(['available_balance' => '10.00']);
        $recipient = $this->makeActiveUser();
        $this->actingAs($sender, 'api');

        $this->postJson('/api/user/wallet/transfers', $this->transferPayload($sender->email))
            ->assertUnprocessable()
            ->assertJsonPath('message', __('messages.wallet.transfer_self_not_allowed'));

        $this->postJson('/api/user/wallet/transfers', $this->transferPayload('missing@example.com'))
            ->assertUnprocessable()
            ->assertJsonPath('message', __('messages.wallet.transfer_recipient_unavailable'));

        $this->postJson('/api/user/wallet/transfers', $this->transferPayload($recipient->email, '11.00'))
            ->assertUnprocessable()
            ->assertJsonPath('message', __('messages.wallet.transfer_amount_exceeds_balance'));

        $inactiveRecipient = UserFactory::new()->create([
            'account_type' => AccountTypeEnum::User,
            'status' => UserStatusEnum::Suspended,
        ]);
        $this->postJson('/api/user/wallet/transfers', $this->transferPayload($inactiveRecipient->email))
            ->assertUnprocessable()
            ->assertJsonPath('message', __('messages.wallet.transfer_recipient_unavailable'));

        $this->assertSame('10.00', (string) Wallet::query()->where('user_id', $sender->id)->value('available_balance'));
        $this->assertDatabaseCount('wallet_transactions', 0);
    }

    public function test_transfer_requires_authentication_and_returns_translated_validation_errors(): void
    {
        $this->postJson('/api/user/wallet/transfers', [])->assertUnauthorized();

        $sender = $this->makeActiveUser(['available_balance' => '100.00']);
        $recipient = $this->makeActiveUser();

        $this->actingAs($sender, 'api')
            ->withHeader('Accept-Language', 'ar')
            ->postJson('/api/user/wallet/transfers', [
                'recipient' => $recipient->email,
                'amount' => '1.001',
                'idempotency_key' => (string) Str::uuid(),
            ])
            ->assertUnprocessable()
            ->assertJsonPath('message', __('messages.validation.wallet_transfer.amount.decimal'));

        $this->withHeader('Accept-Language', 'en')
            ->postJson('/api/user/wallet/transfers', [
                'recipient' => $recipient->email,
                'amount' => '10.00',
                'idempotency_key' => 'not-a-uuid',
            ])
            ->assertUnprocessable()
            ->assertJsonPath('message', __('messages.validation.wallet_transfer.idempotency_key.uuid'));
    }

    /** @param array<string, string> $walletBalances
     * @param  array<string, mixed>  $userAttributes
     */
    private function makeActiveUser(array $walletBalances = [], array $userAttributes = []): User
    {
        $user = UserFactory::new()->create([
            'account_type' => AccountTypeEnum::User,
            'status' => UserStatusEnum::Active,
            ...$userAttributes,
        ]);

        Wallet::query()->create([
            'user_id' => $user->id,
            'currency' => 'EGP',
            'available_balance' => $walletBalances['available_balance'] ?? '0.00',
            'pending_balance' => $walletBalances['pending_balance'] ?? '0.00',
            'is_active' => true,
        ]);

        return $user;
    }

    /** @return array{recipient: string, amount: string, idempotency_key: string} */
    private function transferPayload(string $recipient, string $amount = '5.00'): array
    {
        return [
            'recipient' => $recipient,
            'amount' => $amount,
            'idempotency_key' => (string) Str::uuid(),
        ];
    }
}
