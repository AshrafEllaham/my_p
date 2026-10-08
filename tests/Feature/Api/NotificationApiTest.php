<?php

namespace Tests\Feature\Api;

use App\Enums\AccountTypeEnum;
use App\Models\Sai\Notification;
use App\Models\Sai\User;
use Database\Factories\UserFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_notification_routes_require_authentication(): void
    {
        $this->getJson('/api/notifications')->assertUnauthorized();
        $this->patchJson('/api/notifications/read-all')->assertUnauthorized();
        $this->deleteJson('/api/notifications')->assertUnauthorized();
        $this->getJson('/api/notifications/preferences')->assertUnauthorized();
        $this->putJson('/api/notifications/preferences')->assertUnauthorized();
    }

    public function test_user_and_store_accounts_can_list_only_their_notifications(): void
    {
        $user = UserFactory::new()->create(['account_type' => AccountTypeEnum::User]);
        $store = UserFactory::new()->create(['account_type' => AccountTypeEnum::Store]);
        $userNotification = $this->createNotification($user, 'User notification');
        $storeNotification = $this->createNotification($store, 'Store notification');

        $this->withToken($this->tokenFor($user))
            ->getJson('/api/notifications?unread_only=1&per_page=10')
            ->assertOk()
            ->assertJsonPath('data.0.id', $userNotification->id)
            ->assertJsonPath('data.0.title', 'User notification')
            ->assertJsonPath('meta.per_page', 10)
            ->assertJsonMissing(['id' => $storeNotification->id]);

        $this->withToken($this->tokenFor($store))
            ->getJson('/api/notifications')
            ->assertOk()
            ->assertJsonPath('data.0.id', $storeNotification->id);
    }

    public function test_account_can_mark_own_notifications_as_read(): void
    {
        $user = UserFactory::new()->create();
        $other = UserFactory::new()->create();
        $notification = $this->createNotification($user, 'Read me');
        $otherNotification = $this->createNotification($other, 'Private');
        $token = $this->tokenFor($user);

        $this->withToken($token)
            ->patchJson("/api/notifications/{$notification->id}/read")
            ->assertOk()
            ->assertJsonPath('data.is_read', true);

        $this->assertDatabaseMissing('notifications', [
            'id' => $notification->id,
            'read_at' => null,
        ]);

        $this->withToken($token)
            ->patchJson("/api/notifications/{$otherNotification->id}/read")
            ->assertNotFound();
    }

    public function test_account_can_mark_all_and_delete_only_its_notifications(): void
    {
        $user = UserFactory::new()->create();
        $other = UserFactory::new()->create();
        $first = $this->createNotification($user, 'First');
        $second = $this->createNotification($user, 'Second');
        $otherNotification = $this->createNotification($other, 'Other');
        $token = $this->tokenFor($user);

        $this->withToken($token)
            ->patchJson('/api/notifications/read-all')
            ->assertOk()
            ->assertJsonPath('data.updated', 2);

        $this->assertNotNull($first->fresh()?->read_at);
        $this->assertNotNull($second->fresh()?->read_at);
        $this->assertNull($otherNotification->fresh()?->read_at);

        $this->withToken($token)
            ->deleteJson('/api/notifications')
            ->assertOk()
            ->assertJsonPath('data.deleted', 2);

        $this->assertDatabaseMissing('notifications', ['id' => $first->id]);
        $this->assertDatabaseHas('notifications', ['id' => $otherNotification->id]);
    }

    public function test_account_can_get_and_update_notification_preferences(): void
    {
        $user = UserFactory::new()->create();
        $token = $this->tokenFor($user);

        $this->withToken($token)
            ->getJson('/api/notifications/preferences')
            ->assertOk()
            ->assertJsonPath('data.orders_enabled', true)
            ->assertJsonPath('data.offers_enabled', true);

        $preferences = [
            'orders_enabled' => true,
            'pickup_enabled' => false,
            'returns_enabled' => true,
            'chats_enabled' => false,
            'offers_enabled' => false,
        ];

        $this->withToken($token)
            ->putJson('/api/notifications/preferences', $preferences)
            ->assertOk()
            ->assertJsonPath('data.pickup_enabled', false)
            ->assertJsonPath('data.offers_enabled', false);

        $this->assertDatabaseHas('notification_preferences', [
            'user_id' => $user->id,
            'pickup_enabled' => false,
            'offers_enabled' => false,
        ]);
    }

    public function test_account_can_update_one_notification_preference_without_overwriting_others(): void
    {
        $user = UserFactory::new()->create();
        $token = $this->tokenFor($user);

        $this->withToken($token)
            ->getJson('/api/notifications/preferences')
            ->assertOk();

        $this->withToken($token)
            ->patchJson('/api/notifications/preferences', ['chats_enabled' => false])
            ->assertOk()
            ->assertJsonPath('data.chats_enabled', false)
            ->assertJsonPath('data.orders_enabled', true)
            ->assertJsonPath('data.offers_enabled', true);

        $this->assertDatabaseHas('notification_preferences', [
            'user_id' => $user->id,
            'chats_enabled' => false,
            'orders_enabled' => true,
            'offers_enabled' => true,
        ]);

        $this->withToken($token)
            ->patchJson('/api/notifications/preferences', ['chats_enabled' => true])
            ->assertOk()
            ->assertJsonPath('data.chats_enabled', true);

        $this->assertDatabaseHas('notification_preferences', [
            'user_id' => $user->id,
            'chats_enabled' => true,
        ]);
    }

    public function test_notification_validation_messages_are_localized(): void
    {
        $user = UserFactory::new()->create();
        $token = $this->tokenFor($user);

        $this->withHeader('Accept-Language', 'en')
            ->withToken($token)
            ->getJson('/api/notifications?per_page=101')
            ->assertUnprocessable()
            ->assertJsonPath('message', __('messages.validation.notifications_per_page.max'));

        $this->withHeader('Accept-Language', 'ar')
            ->withToken($token)
            ->putJson('/api/notifications/preferences', [])
            ->assertUnprocessable()
            ->assertJsonPath('message', __('messages.validation.notification_preference.required'));

        $this->withHeader('Accept-Language', 'en')
            ->withToken($token)
            ->patchJson('/api/notifications/preferences', ['chats_enabled' => 'enabled'])
            ->assertUnprocessable()
            ->assertJsonPath('message', __('messages.validation.notification_preference.boolean'));

        $this->withHeader('Accept-Language', 'ar')
            ->withToken($token)
            ->patchJson('/api/notifications/preferences', [])
            ->assertUnprocessable()
            ->assertJsonPath('message', __('messages.validation.notification_preference.at_least_one'));
    }

    public function test_notification_preview_screens_use_the_shared_api_routes(): void
    {
        $userScreen = file_get_contents(base_path('Figma/screens/13-notifications.html'));
        $storeScreen = file_get_contents(base_path('Figma/screens/24-distributor-notifications.html'));
        $settingsScreen = file_get_contents(base_path('Figma/screens/account-notification-settings.html'));
        $apiScript = file_get_contents(base_path('Figma/screens/notifications-api.js'));

        $this->assertIsString($userScreen);
        $this->assertIsString($storeScreen);
        $this->assertIsString($settingsScreen);
        $this->assertIsString($apiScript);
        $this->assertStringContainsString('notifications-api.js', $userScreen);
        $this->assertStringContainsString('notifications-api.js', $storeScreen);
        $this->assertStringContainsString('notifications-api.js', $settingsScreen);
        $this->assertStringContainsString('/api/notifications/read-all', $apiScript);
        $this->assertStringContainsString('/api/notifications/preferences', $apiScript);
        $this->assertStringContainsString("method: 'PATCH'", $apiScript);
    }

    private function createNotification(User $user, string $title): Notification
    {
        return Notification::query()->create([
            'notifiable_type' => User::class,
            'notifiable_id' => $user->id,
            'type' => 'order',
            'data' => [
                'title' => $title,
                'message' => 'Notification body',
                'action_url' => '/orders/1',
            ],
        ]);
    }

    private function tokenFor(User $user): string
    {
        return (string) auth('api')->login($user);
    }
}
