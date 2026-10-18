<?php

namespace Tests\Feature\Api;

use App\Enums\AccountTypeEnum;
use App\Enums\ConversationSafetyActionTypeEnum;
use App\Enums\UserStatusEnum;
use App\Events\Sai\Chat\ChatMessageSent;
use App\Models\Sai\Category;
use App\Models\Sai\Conversation;
use App\Models\Sai\Store;
use App\Models\Sai\User;
use Database\Factories\UserFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class ChatApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Event::fake([ChatMessageSent::class]);
    }

    public function test_user_opens_one_room_per_store_and_both_participants_can_list_it(): void
    {
        $user = $this->account('مستخدم سعي', AccountTypeEnum::User);
        $store = $this->storeAccount('متجر سعي');
        $first = $this->withToken($this->tokenFor($user))
            ->postJson('/api/chat/rooms', ['store_id' => $store->id])
            ->assertOk()
            ->assertJsonPath('data.store.id', $store->id)
            ->assertJsonPath('data.user.id', $user->id)
            ->json('data.id');

        $second = $this->withToken($this->tokenFor($user))
            ->postJson('/api/chat/rooms', ['store_id' => $store->id])
            ->assertOk()
            ->json('data.id');

        $this->assertSame($first, $second);
        $this->assertDatabaseCount('conversations', 1);

        $this->withToken($this->tokenFor($user))
            ->getJson('/api/chat/rooms?search=متجر&limit_per_page=1')
            ->assertOk()
            ->assertJsonPath('data.0.id', $first)
            ->assertJsonPath('per_page', 1);

        $this->withToken($this->tokenFor($store))
            ->getJson('/api/chat/rooms?search=مستخدم')
            ->assertOk()
            ->assertJsonPath('data.0.user.name', 'مستخدم سعي')
            ->assertJsonPath('data.0.store.id', $store->id);
    }

    public function test_rooms_are_sorted_by_latest_message_and_messages_are_searchable_and_read(): void
    {
        $user = $this->account('المستخدم', AccountTypeEnum::User);
        $storeOne = $this->storeAccount('المتجر الأول');
        $storeTwo = $this->storeAccount('المتجر الثاني');
        $roomOne = $this->createRoom($user, $storeOne);
        $roomTwo = $this->createRoom($user, $storeTwo);

        $this->withToken($this->tokenFor($user))->postJson("/api/chat/rooms/{$roomOne}/messages", ['body' => 'مرحبا بالمتجر الأول'])
            ->assertOk();
        $this->withToken($this->tokenFor($user))->postJson("/api/chat/rooms/{$roomTwo}/messages", ['body' => 'استفسار جديد'])
            ->assertOk();
        $this->withToken($this->tokenFor($storeTwo))->postJson("/api/chat/rooms/{$roomTwo}/messages", ['body' => 'رد المتجر'])
            ->assertOk();

        Event::assertDispatched(ChatMessageSent::class, 3);
        Event::assertDispatched(ChatMessageSent::class, fn (ChatMessageSent $event): bool => $event->roomId === $roomOne
            && $event->messageId > 0
            && count($event->broadcastOn()) === 2
        );

        $this->withToken($this->tokenFor($user))
            ->getJson('/api/chat/rooms')
            ->assertOk()
            ->assertJsonPath('data.0.id', $roomTwo)
            ->assertJsonPath('data.0.unread_messages_count', 1);

        $this->withToken($this->tokenFor($user))
            ->getJson("/api/chat/rooms/{$roomTwo}/messages?limit_per_page=10")
            ->assertOk()
            ->assertJsonPath('data.0.body', 'رد المتجر')
            ->assertJsonPath('data.1.body', 'استفسار جديد');

        $this->withToken($this->tokenFor($user))
            ->getJson("/api/chat/rooms/{$roomTwo}/messages?search=رد&limit_per_page=1")
            ->assertOk()
            ->assertJsonPath('data.0.body', 'رد المتجر')
            ->assertJsonPath('data.0.is_mine', false)
            ->assertJsonPath('data.0.sender.id', $storeTwo->id)
            ->assertJsonPath('per_page', 1);

        $this->assertNotNull(Conversation::query()->where('public_id', $roomTwo)->value('user_read_at'));
        $this->assertDatabaseHas('messages', [
            'conversation_id' => Conversation::query()->where('public_id', $roomTwo)->value('id'),
            'body' => 'رد المتجر',
        ]);

        $this->withToken($this->tokenFor($storeOne))
            ->getJson("/api/chat/rooms/{$roomOne}/messages")
            ->assertOk()
            ->assertJsonPath('data.0.body', 'مرحبا بالمتجر الأول');
    }

    public function test_participant_can_report_and_block_the_room_and_blocking_disables_new_messages(): void
    {
        $user = $this->account('المستخدم', AccountTypeEnum::User);
        $store = $this->storeAccount('المتجر');
        $roomId = $this->createRoom($user, $store);

        $this->withToken($this->tokenFor($user))
            ->postJson("/api/chat/rooms/{$roomId}/reports", [
                'reason' => 'رسائل مزعجة',
                'details' => 'تكرار الرسائل دون طلب.',
            ])
            ->assertOk()
            ->assertJsonPath('data.action', ConversationSafetyActionTypeEnum::Report->value)
            ->assertJsonPath('data.room_id', $roomId);

        $this->withToken($this->tokenFor($user))
            ->postJson("/api/chat/rooms/{$roomId}/blocks")
            ->assertOk()
            ->assertJsonPath('data.action', ConversationSafetyActionTypeEnum::Block->value);
        $this->withToken($this->tokenFor($user))
            ->postJson("/api/chat/rooms/{$roomId}/blocks")
            ->assertOk();

        $this->withToken($this->tokenFor($user))
            ->getJson('/api/chat/rooms')
            ->assertOk()
            ->assertJsonPath('data.0.is_blocked_by_me', true);

        $this->withToken($this->tokenFor($store))
            ->getJson('/api/chat/rooms')
            ->assertOk()
            ->assertJsonPath('data.0.is_blocked_by_other', true);

        $this->assertDatabaseCount('conversation_safety_actions', 2);

        $this->withToken($this->tokenFor($store))
            ->postJson("/api/chat/rooms/{$roomId}/messages", ['body' => 'رد جديد'])
            ->assertForbidden()
            ->assertJsonPath('message', __('messages.chat.room_blocked'));
    }

    public function test_chat_requires_auth_valid_inputs_and_room_participation_with_localized_validation(): void
    {
        $this->getJson('/api/chat/rooms')->assertUnauthorized();
        $this->postJson('/api/chat/rooms', [])->assertUnauthorized();

        $user = $this->account('المستخدم', AccountTypeEnum::User);
        $this->withHeader('Accept-Language', 'ar')->withToken($this->tokenFor($user))
            ->postJson('/api/chat/rooms', [])
            ->assertUnprocessable()
            ->assertJsonPath('message', __('messages.validation.chat.store_id.required'));

        $this->withHeader('Accept-Language', 'en')->withToken($this->tokenFor($user))
            ->getJson('/api/chat/rooms?limit_per_page=51')
            ->assertUnprocessable()
            ->assertJsonPath('message', __('messages.validation.chat.per_page.max'));

        $this->withHeader('Accept-Language', 'ar')->withToken($this->tokenFor($user))
            ->postJson('/api/chat/rooms/not-a-uuid/messages', ['body' => 'test'])
            ->assertUnprocessable()
            ->assertJsonPath('message', __('messages.validation.chat.room.uuid'));

        $store = $this->storeAccount('المتجر');
        $roomId = $this->createRoom($user, $store);
        $this->withHeader('Accept-Language', 'en')->withToken($this->tokenFor($user))
            ->postJson("/api/chat/rooms/{$roomId}/messages", ['body' => '   '])
            ->assertUnprocessable()
            ->assertJsonPath('message', __('messages.validation.chat.body.required'));

        $otherUser = $this->account('مستخدم آخر', AccountTypeEnum::User);
        $this->withToken($this->tokenFor($otherUser))
            ->getJson("/api/chat/rooms/{$roomId}/messages")
            ->assertNotFound();

        $this->withToken($this->tokenFor($store))
            ->postJson('/api/chat/rooms', ['store_id' => $store->id])
            ->assertForbidden();
    }

    public function test_private_inbox_channel_authorization_is_limited_to_the_authenticated_account(): void
    {
        $user = $this->account('المستخدم', AccountTypeEnum::User);
        $anotherUser = $this->account('مستخدم آخر', AccountTypeEnum::User);
        $channel = 'private-chat.inboxes.'.$user->id;
        $requestData = ['socket_id' => '123.456', 'channel_name' => $channel];

        $this->withToken($this->tokenFor($user))
            ->postJson('/api/broadcasting/auth', $requestData)
            ->assertOk()
            ->assertJsonStructure(['auth']);

        $this->withToken($this->tokenFor($anotherUser))
            ->postJson('/api/broadcasting/auth', $requestData)
            ->assertForbidden();
    }

    public function test_chat_preview_uses_the_room_message_report_and_block_endpoints(): void
    {
        $roomList = file_get_contents(base_path('Figma/screens/14-chats.html'));
        $conversation = file_get_contents(base_path('Figma/screens/14-chat-conversation.html'));
        $api = file_get_contents(base_path('Figma/screens/chat-api.js'));
        $preview = file_get_contents(base_path('Figma/preview.html'));

        $this->assertIsString($roomList);
        $this->assertIsString($conversation);
        $this->assertIsString($api);
        $this->assertIsString($preview);
        $this->assertStringContainsString('chat-api.js', $roomList);
        $this->assertStringContainsString('chat-api.js', $conversation);
        $this->assertStringContainsString('chat-reverb.js', $roomList);
        $this->assertStringContainsString('chat-reverb.js', $conversation);
        $this->assertStringContainsString("'chat.inboxes.'", $api);
        $this->assertStringContainsString("request('/chat/rooms'", $api);
        $this->assertStringContainsString("'/messages?'", $api);
        $this->assertStringContainsString("'/reports'", $api);
        $this->assertStringContainsString("'/blocks'", $api);
        $this->assertStringContainsString("file: '14-chats.html'", $preview);
        $this->assertStringContainsString("file: '14-chat-conversation.html'", $preview);
    }

    private function account(string $name, AccountTypeEnum $type): User
    {
        return UserFactory::new()->create([
            'name' => $name,
            'account_type' => $type,
            'status' => UserStatusEnum::Active,
        ]);
    }

    private function storeAccount(string $name): User
    {
        $owner = $this->account($name, AccountTypeEnum::Store);
        Store::query()->create([
            'owner_id' => $owner->id,
            'category_id' => Category::factory()->create()->id,
        ]);

        return $owner;
    }

    private function createRoom(User $user, User $store): string
    {
        return (string) $this->withToken($this->tokenFor($user))
            ->postJson('/api/chat/rooms', ['store_id' => $store->id])
            ->assertOk()
            ->json('data.id');
    }

    private function tokenFor(User $user): string
    {
        $token = (string) auth('api')->login($user);
        app('auth')->forgetGuards();

        return $token;
    }
}
