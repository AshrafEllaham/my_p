<?php

namespace Tests\Feature;

use App\Models\Twenty\Category;
use App\Models\Twenty\City;
use App\Models\Twenty\Country;
use App\Models\Twenty\CountryTranslation;
use App\Models\Twenty\Governorate;
use Database\Seeders\CategorySeeder;
use Database\Seeders\CitySeeder;
use Database\Seeders\CountrySeeder;
use Database\Seeders\GovernorateSeeder;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class CoreMigrationsTest extends TestCase
{
    use RefreshDatabase;

    public function test_core_tables_and_translation_tables_exist(): void
    {
        $this->assertTrue(Schema::hasTable('countries'));
        $this->assertTrue(Schema::hasTable('country_translations'));

        $this->assertTrue(Schema::hasTable('governorates'));
        $this->assertTrue(Schema::hasTable('governorate_translations'));

        $this->assertTrue(Schema::hasTable('cities'));
        $this->assertTrue(Schema::hasTable('city_translations'));

        $this->assertTrue(Schema::hasTable('categories'));
        $this->assertTrue(Schema::hasTable('category_translations'));
    }

    public function test_country_model_and_translations_work_correctly(): void
    {
        $country = Country::create([
            'code' => 'EG',
            'phone_code' => '+20',
            'flag' => '🇪🇬',
            'is_active' => true,
            'ar' => ['name' => 'مصر'],
            'en' => ['name' => 'Egypt'],
        ]);

        $this->assertDatabaseHas('countries', [
            'id' => $country->id,
            'code' => 'EG',
            'phone_code' => '+20',
            'is_active' => true,
        ]);

        $this->assertDatabaseHas('country_translations', [
            'country_id' => $country->id,
            'locale' => 'ar',
            'name' => 'مصر',
        ]);

        $this->assertDatabaseHas('country_translations', [
            'country_id' => $country->id,
            'locale' => 'en',
            'name' => 'Egypt',
        ]);

        App::setLocale('ar');
        $this->assertSame('مصر', $country->name);

        App::setLocale('en');
        $this->assertSame('Egypt', $country->name);
    }

    public function test_governorates_and_cities_hierarchy_and_translations(): void
    {
        $country = Country::create([
            'code' => 'EG',
            'phone_code' => '+20',
            'flag' => '🇪🇬',
            'is_active' => true,
            'ar' => ['name' => 'مصر'],
            'en' => ['name' => 'Egypt'],
        ]);

        $governorate = Governorate::create([
            'country_id' => $country->id,
            'is_active' => true,
            'ar' => ['name' => 'القاهرة'],
            'en' => ['name' => 'Cairo'],
        ]);

        $city = City::create([
            'governorate_id' => $governorate->id,
            'is_active' => true,
            'ar' => ['name' => 'مدينة نصر'],
            'en' => ['name' => 'Nasr City'],
        ]);

        $this->assertTrue($country->governorates->contains($governorate));
        $this->assertTrue($governorate->cities->contains($city));
        $this->assertSame($country->id, $governorate->country->id);
        $this->assertSame($governorate->id, $city->governorate->id);

        App::setLocale('ar');
        $this->assertSame('القاهرة', $governorate->name);
        $this->assertSame('مدينة نصر', $city->name);

        App::setLocale('en');
        $this->assertSame('Cairo', $governorate->name);
        $this->assertSame('Nasr City', $city->name);
    }

    public function test_categories_hierarchical_structure_and_translations(): void
    {
        $mainCategory = Category::create([
            'parent_id' => null,
            'slug' => 'fashion',
            'icon' => 'shirt',
            'is_active' => true,
            'sort_order' => 1,
            'ar' => [
                'name' => 'أزياء وملابس',
                'description' => 'كل ما يخص الأزياء والملابس',
            ],
            'en' => [
                'name' => 'Fashion & Clothing',
                'description' => 'All fashion and clothing items',
            ],
        ]);

        $subCategory = Category::create([
            'parent_id' => $mainCategory->id,
            'slug' => 'mens-clothing',
            'icon' => 'user',
            'is_active' => true,
            'sort_order' => 1,
            'ar' => [
                'name' => 'ملابس رجالي',
                'description' => 'تشكيلة ملابس رجالية',
            ],
            'en' => [
                'name' => 'Men Fashion',
                'description' => 'Men clothing collection',
            ],
        ]);

        $this->assertTrue($mainCategory->children->contains($subCategory));
        $this->assertSame($mainCategory->id, $subCategory->parent->id);

        App::setLocale('ar');
        $this->assertSame('أزياء وملابس', $mainCategory->name);
        $this->assertSame('ملابس رجالي', $subCategory->name);

        App::setLocale('en');
        $this->assertSame('Fashion & Clothing', $mainCategory->name);
        $this->assertSame('Men Fashion', $subCategory->name);
    }

    public function test_translation_unique_index_prevents_duplicate_locale_per_entity(): void
    {
        $country = Country::create([
            'code' => 'SA',
            'phone_code' => '+966',
            'flag' => '🇸🇦',
            'is_active' => true,
            'ar' => ['name' => 'السعودية'],
        ]);

        $this->expectException(QueryException::class);

        CountryTranslation::create([
            'country_id' => $country->id,
            'locale' => 'ar',
            'name' => 'المملكة العربية السعودية',
        ]);
    }

    public function test_deleting_parent_entity_cascades_to_translations_and_children(): void
    {
        $country = Country::create([
            'code' => 'EG',
            'phone_code' => '+20',
            'is_active' => true,
            'ar' => ['name' => 'مصر'],
        ]);

        $gov = Governorate::create([
            'country_id' => $country->id,
            'is_active' => true,
            'ar' => ['name' => 'الجيزة'],
        ]);

        $countryId = $country->id;
        $govId = $gov->id;

        $country->delete();

        $this->assertDatabaseMissing('countries', ['id' => $countryId]);
        $this->assertDatabaseMissing('country_translations', ['country_id' => $countryId]);
        $this->assertDatabaseMissing('governorates', ['id' => $govId]);
        $this->assertDatabaseMissing('governorate_translations', ['governorate_id' => $govId]);
    }

    public function test_individual_seeders_populate_complete_data(): void
    {
        $this->seed(CountrySeeder::class);
        $this->assertSame(1, Country::count());
        $this->assertDatabaseHas('countries', ['code' => 'EG']);
        $this->assertDatabaseHas('country_translations', ['locale' => 'ar', 'name' => 'مصر']);
        $this->assertDatabaseHas('country_translations', ['locale' => 'en', 'name' => 'Egypt']);

        $this->seed(GovernorateSeeder::class);
        $this->assertSame(27, Governorate::count());
        $this->assertDatabaseHas('governorate_translations', ['locale' => 'ar', 'name' => 'القاهرة']);
        $this->assertDatabaseHas('governorate_translations', ['locale' => 'ar', 'name' => 'الإسكندرية']);
        $this->assertDatabaseHas('governorate_translations', ['locale' => 'ar', 'name' => 'أسوان']);
        $this->assertDatabaseHas('governorate_translations', ['locale' => 'en', 'name' => 'Cairo']);

        $this->seed(CitySeeder::class);
        $this->assertGreaterThanOrEqual(300, City::count());
        $this->assertDatabaseHas('city_translations', ['locale' => 'ar', 'name' => 'مدينة نصر']);
        $this->assertDatabaseHas('city_translations', ['locale' => 'en', 'name' => 'Nasr City']);
        $this->assertDatabaseHas('city_translations', ['locale' => 'ar', 'name' => 'سموحة']);
        $this->assertDatabaseHas('city_translations', ['locale' => 'en', 'name' => 'Smouha']);

        $cairo = Governorate::whereTranslation('name', 'القاهرة', 'ar')->first();
        $this->assertNotNull($cairo);
        $this->assertGreaterThanOrEqual(20, $cairo->cities()->count());

        $this->seed(CategorySeeder::class);
        $this->assertSame(5, Category::whereNull('parent_id')->count());
        $this->assertSame(24, Category::whereNotNull('parent_id')->count());

        $this->assertDatabaseHas('category_translations', ['locale' => 'ar', 'name' => 'الأزياء والملابس']);
        $this->assertDatabaseHas('category_translations', ['locale' => 'ar', 'name' => 'الإلكترونيات']);
        $this->assertDatabaseHas('category_translations', ['locale' => 'ar', 'name' => 'الإكسسوارات']);
        $this->assertDatabaseHas('category_translations', ['locale' => 'ar', 'name' => 'محركات وقطع غيار']);
        $this->assertDatabaseHas('category_translations', ['locale' => 'ar', 'name' => 'الأطعمة والمشروبات']);
    }
}
