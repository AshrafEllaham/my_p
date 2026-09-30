<?php

namespace Tests\Feature\Localization;

use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Astrotomic\Translatable\Translatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class TranslatableModelTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropIfExists('localization_test_article_translations');
        Schema::dropIfExists('localization_test_articles');

        Schema::create('localization_test_articles', function (Blueprint $table): void {
            $table->id();
            $table->string('slug')->unique();
            $table->timestamps();
        });

        Schema::create('localization_test_article_translations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('localization_test_article_id');
            $table->string('locale', 5);
            $table->string('title');
            $table->foreign('localization_test_article_id', 'lt_article_fk')
                ->references('id')
                ->on('localization_test_articles')
                ->cascadeOnDelete();
            $table->unique(['localization_test_article_id', 'locale'], 'lt_article_locale_unique');
        });
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('localization_test_article_translations');
        Schema::dropIfExists('localization_test_articles');

        parent::tearDown();
    }

    public function test_astrotomic_stores_each_locale_in_the_models_translation_table(): void
    {
        $article = new LocalizationTestArticle(['slug' => 'first-article']);
        $article->translateOrNew('ar')->title = 'المقال الأول';
        $article->translateOrNew('en')->title = 'First article';
        $article->save();

        $this->assertDatabaseCount('localization_test_articles', 1);
        $this->assertDatabaseHas('localization_test_article_translations', [
            'locale' => 'ar',
            'title' => 'المقال الأول',
        ]);
        $this->assertDatabaseHas('localization_test_article_translations', [
            'locale' => 'en',
            'title' => 'First article',
        ]);

        $article->load('translations');

        App::setLocale('ar');
        $this->assertSame('المقال الأول', $article->title);

        App::setLocale('en');
        $this->assertSame('First article', $article->title);
    }

    public function test_only_arabic_and_english_are_configured_for_translatable_models(): void
    {
        $this->assertSame(['ar', 'en'], config('translatable.locales'));
        $this->assertTrue(trait_exists(Translatable::class));
    }
}

class LocalizationTestArticle extends Model implements TranslatableContract
{
    use Translatable;

    protected $table = 'localization_test_articles';

    public array $translatedAttributes = ['title'];

    protected $fillable = ['slug'];
}

class LocalizationTestArticleTranslation extends Model
{
    public $timestamps = false;

    protected $table = 'localization_test_article_translations';

    protected $fillable = ['title'];
}
