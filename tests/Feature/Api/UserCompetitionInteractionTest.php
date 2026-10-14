<?php

namespace Tests\Feature\Api;

use App\Enums\AccountTypeEnum;
use App\Enums\CompetitionStatusEnum;
use App\Enums\UserStatusEnum;
use App\Models\Sai\Category;
use App\Models\Sai\Competition;
use App\Models\Sai\CompetitionEntry;
use App\Models\Sai\Store;
use Database\Factories\UserFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class UserCompetitionInteractionTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_load_competition_details_and_their_saved_answer(): void
    {
        [$store, $customer] = $this->createStoreAndCustomer();
        $competition = $this->createCompetition($store->id, CompetitionStatusEnum::Active, now()->addDay());
        $entry = CompetitionEntry::query()->create([
            'competition_id' => $competition->id,
            'user_id' => $customer->id,
            'answer' => 'إجابة محفوظة',
            'normalized_answer' => 'اجابة محفوظة',
        ]);
        $this->actingAs($customer, 'api');

        $this->getJson("/api/user/competitions/{$competition->id}")
            ->assertOk()
            ->assertJsonPath('message', __('messages.user_competitions.retrieved'))
            ->assertJsonPath('data.id', $competition->id)
            ->assertJsonPath('data.status', 'available_now')
            ->assertJsonPath('data.question', $competition->question)
            ->assertJsonPath('data.participants_count', 1)
            ->assertJsonPath('data.store.name', $store->owner->name)
            ->assertJsonPath('data.my_entry.id', $entry->id)
            ->assertJsonPath('data.my_entry.answer', 'إجابة محفوظة')
            ->assertJsonPath('data.winner', null)
            ->assertJsonMissingPath('data.correct_answer');
    }

    public function test_drawn_competition_details_show_the_winner_but_hide_the_correct_answer(): void
    {
        [$store, $customer] = $this->createStoreAndCustomer();
        $competition = $this->createCompetition($store->id, CompetitionStatusEnum::Drawn, now()->subDay());
        $competition->update([
            'winner_user_id' => $customer->id,
            'correct_answer' => 'الإجابة الصحيحة',
            'normalized_correct_answer' => 'الاجابة الصحيحة',
            'drawn_at' => now(),
        ]);
        $this->actingAs($customer, 'api');

        $this->getJson("/api/user/competitions/{$competition->id}")
            ->assertOk()
            ->assertJsonPath('data.status', 'ended')
            ->assertJsonPath('data.winner.name', $customer->name)
            ->assertJsonPath('data.my_entry', null)
            ->assertJsonMissingPath('data.correct_answer')
            ->assertJsonMissingPath('data.normalized_correct_answer');
    }

    public function test_hidden_or_cancelled_competitions_are_not_available_in_details_or_entry_route(): void
    {
        [$store, $customer] = $this->createStoreAndCustomer();
        $competition = $this->createCompetition($store->id, CompetitionStatusEnum::Cancelled, now()->addDay());
        $hiddenStoreOwner = UserFactory::new()->create([
            'account_type' => AccountTypeEnum::Store,
            'status' => UserStatusEnum::Suspended,
        ]);
        $hiddenStore = Store::query()->create([
            'owner_id' => $hiddenStoreOwner->id,
            'category_id' => $store->category_id,
        ]);
        $hiddenCompetition = $this->createCompetition($hiddenStore->id, CompetitionStatusEnum::Active, now()->addDay());
        $this->actingAs($customer, 'api');

        $this->getJson("/api/user/competitions/{$competition->id}")->assertNotFound();
        $this->putJson("/api/user/competitions/{$competition->id}/entry", ['answer' => 'إجابة صالحة'])->assertNotFound();
        $this->getJson("/api/user/competitions/{$hiddenCompetition->id}")->assertNotFound();
        $this->putJson("/api/user/competitions/{$hiddenCompetition->id}/entry", ['answer' => 'إجابة صالحة'])->assertNotFound();
    }

    public function test_user_can_create_and_update_one_competition_answer(): void
    {
        [$store, $customer] = $this->createStoreAndCustomer();
        $competition = $this->createCompetition($store->id, CompetitionStatusEnum::Active, now()->addDay());
        $this->actingAs($customer, 'api');

        $this->putJson("/api/user/competitions/{$competition->id}/entry", ['answer' => 'إجابة أولى'])
            ->assertOk()
            ->assertJsonPath('message', __('messages.user_competitions.answer_saved'))
            ->assertJsonPath('data.answer', 'إجابة أولى');

        $entryId = CompetitionEntry::query()->where('competition_id', $competition->id)->value('id');

        $this->putJson("/api/user/competitions/{$competition->id}/entry", ['answer' => 'أَزْهَار   حمراء'])
            ->assertOk()
            ->assertJsonPath('data.id', $entryId)
            ->assertJsonPath('data.answer', 'أَزْهَار   حمراء');

        $this->assertDatabaseCount('competition_entries', 1);
        $this->assertDatabaseHas('competition_entries', [
            'id' => $entryId,
            'competition_id' => $competition->id,
            'user_id' => $customer->id,
            'normalized_answer' => 'ازهار حمراء',
        ]);
    }

    public function test_answer_requires_valid_text_and_reports_translated_validation_messages(): void
    {
        [$store, $customer] = $this->createStoreAndCustomer();
        $competition = $this->createCompetition($store->id, CompetitionStatusEnum::Active, now()->addDay());
        $this->actingAs($customer, 'api');

        $this->withHeader('Accept-Language', 'en')
            ->putJson("/api/user/competitions/{$competition->id}/entry", ['answer' => ''])
            ->assertUnprocessable()
            ->assertJsonPath('message', __('messages.validation.user_competitions.answer.required'));

        $this->withHeader('Accept-Language', 'ar')
            ->putJson("/api/user/competitions/{$competition->id}/entry", ['answer' => 'لا'])
            ->assertUnprocessable()
            ->assertJsonPath('message', __('messages.validation.user_competitions.answer.min'));

        $this->putJson("/api/user/competitions/{$competition->id}/entry", ['answer' => str_repeat('س', 181)])
            ->assertUnprocessable()
            ->assertJsonPath('message', __('messages.validation.user_competitions.answer.max'));

        $this->putJson("/api/user/competitions/{$competition->id}/entry", ['answer' => 'َُِّ'])
            ->assertUnprocessable()
            ->assertJsonPath('message', __('messages.validation.user_competitions.answer.regex'));
    }

    public function test_answer_is_rejected_after_expiration_or_draw(): void
    {
        [$store, $customer] = $this->createStoreAndCustomer();
        $expired = $this->createCompetition($store->id, CompetitionStatusEnum::Active, now()->subSecond());
        $drawn = $this->createCompetition($store->id, CompetitionStatusEnum::Drawn, now()->addDay());
        $this->actingAs($customer, 'api');

        $this->getJson("/api/user/competitions/{$expired->id}")
            ->assertOk()
            ->assertJsonPath('data.status', 'ended');

        $this->putJson("/api/user/competitions/{$expired->id}/entry", ['answer' => 'إجابة صالحة'])
            ->assertUnprocessable()
            ->assertJsonPath('message', __('messages.user_competitions.participation_closed'));

        $this->putJson("/api/user/competitions/{$drawn->id}/entry", ['answer' => 'إجابة صالحة'])
            ->assertUnprocessable()
            ->assertJsonPath('message', __('messages.user_competitions.participation_closed'));
    }

    public function test_answer_requires_authentication_and_a_personal_account(): void
    {
        [$store, $customer] = $this->createStoreAndCustomer();
        $competition = $this->createCompetition($store->id, CompetitionStatusEnum::Active, now()->addDay());

        $this->putJson("/api/user/competitions/{$competition->id}/entry", ['answer' => 'إجابة صالحة'])->assertUnauthorized();

        $merchant = $store->owner;
        $this->actingAs($merchant, 'api')
            ->putJson("/api/user/competitions/{$competition->id}/entry", ['answer' => 'إجابة صالحة'])
            ->assertUnprocessable()
            ->assertJsonPath('message', __('messages.profile.user_type_required'));
    }

    public function test_competition_details_preview_calls_both_user_endpoints(): void
    {
        $html = file_get_contents(base_path('Figma/screens/27-competition-details.html'));
        $script = file_get_contents(base_path('Figma/screens/user-competition-details.js'));
        $flow = file_get_contents(base_path('Figma/USER_FLOW_REFERENCE.md'));

        $this->assertIsString($html);
        $this->assertIsString($script);
        $this->assertIsString($flow);
        $this->assertStringContainsString('src="user-competition-details.js"', $html);
        $this->assertStringContainsString('/api/user/competitions/${encodeURIComponent(competitionId)}', $script);
        $this->assertStringContainsString("method: 'PUT'", $script);
        $this->assertStringContainsString('/entry', $script);
        $this->assertStringContainsString('PUT /api/user/competitions/{competition}/entry', $flow);
        $this->assertStringNotContainsString('SaeyState', $script);
    }

    private function createStoreAndCustomer(): array
    {
        $owner = UserFactory::new()->create([
            'account_type' => AccountTypeEnum::Store,
            'status' => UserStatusEnum::Active,
        ]);
        $customer = UserFactory::new()->create([
            'account_type' => AccountTypeEnum::User,
            'status' => UserStatusEnum::Active,
        ]);
        $category = Category::factory()->create(['is_active' => true]);
        $store = Store::query()->create([
            'owner_id' => $owner->id,
            'category_id' => $category->id,
            'cover_image' => 'stores/cover.jpg',
        ]);

        return [$store, $customer];
    }

    private function createCompetition(int $storeId, CompetitionStatusEnum $status, mixed $endsAt): Competition
    {
        return Competition::query()->create([
            'public_id' => (string) Str::uuid(),
            'store_id' => $storeId,
            'content_locale' => 'ar',
            'title' => 'مسابقة المتجر',
            'question' => 'ما هي إجابتك على السؤال؟',
            'prize' => 'قسيمة تسوق',
            'status' => $status,
            'ends_at' => $endsAt,
        ]);
    }
}
