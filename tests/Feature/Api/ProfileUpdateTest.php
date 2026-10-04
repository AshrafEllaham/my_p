<?php

namespace Tests\Feature\Api;

use App\Enums\AccountTypeEnum;
use App\Models\Sai\Category;
use Database\Factories\UserFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProfileUpdateTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
    }

    public function test_profile_endpoints_require_authentication(): void
    {
        $this->postJson('/api/user/profile')->assertUnauthorized();
        $this->postJson('/api/store/profile')->assertUnauthorized();
    }

    public function test_user_can_update_required_name_and_image_with_optional_address(): void
    {
        $user = UserFactory::new()->create(['account_type' => AccountTypeEnum::User]);

        $response = $this->withToken($this->tokenFor($user))->post('/api/user/profile', [
            'name' => 'Ahmed Hassan',
            'image' => UploadedFile::fake()->image('avatar.png', 300, 300),
            'address' => 'Nasr City, Cairo',
        ], ['Accept' => 'application/json']);

        $response
            ->assertOk()
            ->assertJsonPath('data.id', $user->id)
            ->assertJsonPath('data.name', 'Ahmed Hassan')
            ->assertJsonPath('data.address', 'Nasr City, Cairo')
            ->assertJsonPath('message', __('messages.profile.user_updated'));

        $imagePath = $response->json('data.image');

        $this->assertIsString($imagePath);
        Storage::disk('public')->assertExists($imagePath);
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Ahmed Hassan',
            'address_line' => 'Nasr City, Cairo',
            'avatar' => $imagePath,
        ]);
    }

    public function test_user_profile_address_is_optional(): void
    {
        $user = UserFactory::new()->create([
            'account_type' => AccountTypeEnum::User,
            'address_line' => 'Existing address',
        ]);

        $this->withToken($this->tokenFor($user))->post('/api/user/profile', [
            'name' => 'Updated User',
            'image' => UploadedFile::fake()->image('avatar.jpg', 300, 300),
        ], ['Accept' => 'application/json'])->assertOk();

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Updated User',
            'address_line' => 'Existing address',
        ]);
    }

    public function test_store_owner_can_create_and_then_update_one_store_profile(): void
    {
        $owner = UserFactory::new()->create(['account_type' => AccountTypeEnum::Store]);
        $category = Category::factory()->create();
        $token = $this->tokenFor($owner);

        $createResponse = $this->withToken($token)->post('/api/store/profile', [
            'name' => 'Saey Store',
            'image' => UploadedFile::fake()->image('store.png', 400, 400),
            'category_id' => $category->id,
            'description' => 'Store description',
            'cover' => UploadedFile::fake()->image('cover.jpg', 1200, 400),
            'address' => 'Cairo',
        ], ['Accept' => 'application/json']);

        $createResponse
            ->assertOk()
            ->assertJsonPath('data.owner_id', $owner->id)
            ->assertJsonPath('data.name', 'Saey Store')
            ->assertJsonPath('data.category_id', $category->id)
            ->assertJsonPath('data.description', 'Store description')
            ->assertJsonPath('data.address', 'Cairo');

        Storage::disk('public')->assertExists($createResponse->json('data.image'));
        Storage::disk('public')->assertExists($createResponse->json('data.cover'));

        $this->withToken($token)->post('/api/store/profile', [
            'name' => 'Updated Store',
            'image' => UploadedFile::fake()->image('updated-store.webp', 400, 400),
            'category_id' => $category->id,
        ], ['Accept' => 'application/json'])
            ->assertOk()
            ->assertJsonPath('data.name', 'Updated Store')
            ->assertJsonPath('data.description', 'Store description')
            ->assertJsonPath('data.address', 'Cairo');

        $this->assertDatabaseCount('stores', 1);
        $this->assertDatabaseHas('users', [
            'id' => $owner->id,
            'name' => 'Updated Store',
            'address_line' => 'Cairo',
        ]);
        $this->assertDatabaseHas('stores', [
            'owner_id' => $owner->id,
            'category_id' => $category->id,
            'description' => 'Store description',
        ]);
        $this->assertFalse(Schema::hasColumn('stores', 'name'));
        $this->assertFalse(Schema::hasColumn('stores', 'image'));
        $this->assertFalse(Schema::hasColumn('stores', 'address'));
    }

    public function test_profile_validation_messages_are_localized(): void
    {
        $user = UserFactory::new()->create(['account_type' => AccountTypeEnum::User]);

        $this->withHeader('Accept-Language', 'en')
            ->withToken($this->tokenFor($user))
            ->postJson('/api/user/profile', [])
            ->assertUnprocessable()
            ->assertJsonPath('errors.name.0', __('messages.validation.profile_name.required'))
            ->assertJsonPath('errors.image.0', __('messages.validation.profile_image.required'));

        $owner = UserFactory::new()->create(['account_type' => AccountTypeEnum::Store]);

        $this->withHeader('Accept-Language', 'ar')
            ->withToken($this->tokenFor($owner))
            ->postJson('/api/store/profile', [])
            ->assertUnprocessable()
            ->assertJsonPath('errors.name.0', __('messages.validation.profile_name.required'))
            ->assertJsonPath('errors.image.0', __('messages.validation.profile_image.required'))
            ->assertJsonPath('errors.category_id.0', __('messages.validation.category_id.required'));
    }

    public function test_profile_routes_reject_the_wrong_account_type(): void
    {
        $storeAccount = UserFactory::new()->create(['account_type' => AccountTypeEnum::Store]);
        $category = Category::factory()->create();

        $this->withToken($this->tokenFor($storeAccount))->post('/api/user/profile', [
            'name' => 'Wrong Role',
            'image' => UploadedFile::fake()->image('avatar.png'),
        ], ['Accept' => 'application/json'])
            ->assertUnprocessable()
            ->assertJsonPath('errors.account_type.0', __('messages.profile.user_type_required'));

        $userAccount = UserFactory::new()->create(['account_type' => AccountTypeEnum::User]);

        $this->withToken($this->tokenFor($userAccount))->post('/api/store/profile', [
            'name' => 'Wrong Role Store',
            'image' => UploadedFile::fake()->image('store.png'),
            'category_id' => $category->id,
        ], ['Accept' => 'application/json'])
            ->assertUnprocessable()
            ->assertJsonPath('errors.account_type.0', __('messages.profile.store_type_required'));
    }

    public function test_figma_profile_screens_submit_to_the_api_endpoints(): void
    {
        $userScreen = file_get_contents(base_path('Figma/screens/08-profile-setup.html'));
        $storeScreen = file_get_contents(base_path('Figma/screens/06-distributor-setup.html'));

        $this->assertIsString($userScreen);
        $this->assertIsString($storeScreen);
        $this->assertStringContainsString('/api/user/profile', $userScreen);
        $this->assertStringContainsString("body.append('image', image)", $userScreen);
        $this->assertStringContainsString('/api/store/profile', $storeScreen);
        $this->assertStringContainsString("body.append('category_id'", $storeScreen);
    }

    private function tokenFor(object $user): string
    {
        return (string) auth('api')->login($user);
    }
}
