<?php

namespace Database\Seeders;

use App\Enums\AccountTypeEnum;
use App\Enums\AdActionEnum;
use App\Enums\AdPlacementEnum;
use App\Enums\AdStatusEnum;
use App\Enums\CompetitionStatusEnum;
use App\Enums\CouponDiscountTypeEnum;
use App\Enums\CouponStatusEnum;
use App\Enums\MediaTypeEnum;
use App\Enums\ProductStatusEnum;
use App\Enums\UserStatusEnum;
use App\Models\Sai\Ad;
use App\Models\Sai\AdPackage;
use App\Models\Sai\Category;
use App\Models\Sai\City;
use App\Models\Sai\Competition;
use App\Models\Sai\Coupon;
use App\Models\Sai\Governorate;
use App\Models\Sai\Product;
use App\Models\Sai\ProductFeature;
use App\Models\Sai\ProductMedia;
use App\Models\Sai\Store;
use App\Models\Sai\StorePolicyVersion;
use App\Models\Sai\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class DemoStoreCatalogSeeder extends Seeder
{
    /** @var array<int, string> */
    private const PRODUCT_IMAGES = [
        'banners/K8dVPORwDdqugEdMhtBU41xdRSrvLbfYseH4CpWc.webp',
        'banners/ZxyvV6camKqL7Lu6DhjtyGhMtbAqYjpPC7FJawdz.webp',
        'banners/6MUOH6WJICN73jvjojZjPYdRFPUOe2wdu9dl3Asd.webp',
        'banners/icSwbHMf08H93N2n8h7ViFi5b7ctzHtVPX2sjhUJ.png',
        'ads/U2NyZWVuc2hvdCBmcm9tIDIwMjYtMDktMzAgMjAtNDQtMTQ=_1791636523.webp',
        'ads/U2NyZWVuc2hvdCBmcm9tIDIwMjYtMDktMzAgMjAtNDQtMTQ=_1791635865.webp',
    ];

    /** @var array<int, string> */
    private const STORE_AVATARS = [
        'users/avatars/bnVtYmVyLW9uZQ==_1791913941.webp',
        'users/avatars/c2FleS1wcmV2aWV3LWF2YXRhcg==_1791447564.webp',
        'users/avatars/U2NyZWVuc2hvdCBmcm9tIDIwMjYtMDktMzAgMjAtNDQtMTQ=_1791635734.webp',
        'users/avatars/TmFtaV9uZXdfc2l0ZQ==_1791480394.webp',
    ];

    /** @var array<int, string> */
    private const STORE_COVERS = [
        'stores/covers/U2NyZWVuc2hvdCBmcm9tIDIwMjYtMTAtMDkgMTQtNDUtNTQ=_1791635734.webp',
        'banners/K8dVPORwDdqugEdMhtBU41xdRSrvLbfYseH4CpWc.webp',
        'banners/ZxyvV6camKqL7Lu6DhjtyGhMtbAqYjpPC7FJawdz.webp',
        'banners/6MUOH6WJICN73jvjojZjPYdRFPUOe2wdu9dl3Asd.webp',
        'banners/icSwbHMf08H93N2n8h7ViFi5b7ctzHtVPX2sjhUJ.png',
    ];

    /**
     * Add five repeatable demo stores with catalog and user-facing content.
     *
     * Run with: php artisan db:seed --class=DemoStoreCatalogSeeder
     */
    public function run(): void
    {
        if (! app()->environment(['local', 'testing'])) {
            throw new RuntimeException('Demo store data can only be seeded in local or testing environments.');
        }

        DB::transaction(function (): void {
            $categories = $this->mainCategories();
            $adPackage = AdPackage::query()->where('is_active', true)->orderBy('id')->first();

            if (! $adPackage) {
                throw new RuntimeException('DemoStoreCatalogSeeder requires an active ad package.');
            }

            $locations = $this->cairoLocations();
            $firstHomeAdStartsAt = now()->subDay();

            foreach ($this->stores() as $index => $definition) {
                $mainCategory = $categories[$definition['category']];
                $products = $this->seedStore($index + 1, $definition, $mainCategory, $locations);

                $this->seedAds($index + 1, $definition, $mainCategory, $products, $adPackage, $firstHomeAdStartsAt);
                $this->seedCompetition($index + 1, $definition);
                $this->seedCoupon($index + 1);
            }
        });
    }

    /** @return array<string, Category> */
    private function mainCategories(): array
    {
        $categories = Category::query()
            ->whereNull('parent_id')
            ->where('is_active', true)
            ->with(['translations', 'children.translations'])
            ->get()
            ->keyBy(fn (Category $category): ?string => $category->translate('ar')?->name);

        foreach ($this->stores() as $definition) {
            $category = $categories->get($definition['category']);

            if (! $category) {
                throw new RuntimeException("DemoStoreCatalogSeeder could not find active main category: {$definition['category']}");
            }

            foreach ($definition['products'] as $product) {
                $child = $category->children->first(
                    fn (Category $item): bool => $item->translate('ar')?->name === $product['category'],
                );

                if (! $child) {
                    throw new RuntimeException("DemoStoreCatalogSeeder could not find category: {$product['category']}");
                }
            }
        }

        return $categories->all();
    }

    /** @return array{governorate_id: int|null, city_ids: array<int, int>} */
    private function cairoLocations(): array
    {
        $governorate = Governorate::query()
            ->where('is_active', true)
            ->with('translations')
            ->get()
            ->first(fn (Governorate $item): bool => $item->translate('ar')?->name === 'القاهرة');

        if (! $governorate) {
            return ['governorate_id' => null, 'city_ids' => []];
        }

        return [
            'governorate_id' => (int) $governorate->id,
            'city_ids' => City::query()
                ->where('governorate_id', $governorate->id)
                ->where('is_active', true)
                ->orderBy('id')
                ->pluck('id')
                ->map(fn ($id): int => (int) $id)
                ->all(),
        ];
    }

    /** @param array<string, mixed> $definition
     * @param  array{governorate_id: int|null, city_ids: array<int, int>}  $locations
     * @return array<int, Product>
     */
    private function seedStore(int $number, array $definition, Category $mainCategory, array $locations): array
    {
        $email = "store{$number}@gmail.com";
        $user = User::withTrashed()->firstOrNew(['email' => $email]);

        if ($user->trashed()) {
            $user->restore();
        }

        $user->fill([
            'name' => $definition['name'],
            'phone_code' => '+20',
            'phone' => '100000000'.($number + 10),
            'password' => '12345678',
            'account_type' => AccountTypeEnum::Store,
            'status' => UserStatusEnum::Active,
            'avatar' => self::STORE_AVATARS[($number - 1) % count(self::STORE_AVATARS)],
            'preferred_locale' => 'ar',
            'address_line' => 'القاهرة، مصر',
            'governorate_id' => $locations['governorate_id'],
            'city_id' => $locations['city_ids'] === [] ? null : $locations['city_ids'][($number - 1) % count($locations['city_ids'])],
        ]);
        $user->save();

        $store = Store::query()->firstOrNew(['owner_id' => $user->id]);
        $store->fill([
            'category_id' => $mainCategory->id,
            'description' => $definition['description'],
            'cover_image' => self::STORE_COVERS[$number - 1],
            'contact_phone' => '+2010000000'.($number + 10),
            'contact_email' => $email,
            'latitude' => 30.0444 + ($number * 0.006),
            'longitude' => 31.2357 + ($number * 0.007),
            'is_featured' => in_array($number, [1, 3, 5], true),
        ]);
        $store->save();

        StorePolicyVersion::query()->updateOrCreate(
            ['store_id' => $user->id, 'version' => 1],
            [
                'pickup_hold_hours' => 24,
                'return_window_days' => 7,
                'returns_enabled' => true,
                'exchanges_enabled' => true,
                'cancellation_enabled' => true,
                'pickup_instructions' => 'تواصل مع المتجر لتأكيد موعد الاستلام قبل التوجه إليه.',
                'return_terms' => 'يمكن طلب الإرجاع خلال 7 أيام من استلام الطلب، بشرط سلامة المنتج.',
                'exchange_terms' => 'يمكن طلب استبدال المنتج خلال 7 أيام وفقًا لتوفر البديل.',
                'cancellation_terms' => 'يمكن إلغاء الطلب قبل بدء تجهيزه لدى المتجر.',
                'is_current' => true,
                'effective_at' => now()->subDays(2),
            ],
        );

        return collect($definition['products'])
            ->values()
            ->map(function (array $productDefinition, int $productIndex) use ($number, $user, $mainCategory): Product {
                $category = $mainCategory->children->first(
                    fn (Category $item): bool => $item->translate('ar')?->name === $productDefinition['category'],
                );
                $sku = "DEMO-STORE-{$number}-P".($productIndex + 1);
                $product = Product::withTrashed()->firstOrNew(['store_id' => $user->id, 'sku' => $sku]);

                if ($product->trashed()) {
                    $product->restore();
                }

                $price = $productDefinition['price'];
                $originalPrice = $productDefinition['original_price'];
                $product->fill([
                    'category_id' => $category->id,
                    'name' => $productDefinition['name'],
                    'description' => $productDefinition['description'],
                    'slug' => "demo-store-{$number}-product-".($productIndex + 1),
                    'status' => ProductStatusEnum::Published,
                    'price' => $price,
                    'original_price' => $originalPrice,
                    'discount_percentage' => $originalPrice === null ? null : round((1 - ($price / $originalPrice)) * 100, 2),
                    'discount_ends_at' => $originalPrice === null ? null : now()->addDays(30),
                    'stock_quantity' => $productDefinition['stock'],
                    'low_stock_threshold' => 5,
                    'is_featured' => $productIndex === 0,
                    'published_at' => now()->subDays($productIndex + $number),
                ]);
                $product->save();

                $imagePath = self::PRODUCT_IMAGES[(($number - 1) * 3 + $productIndex) % count(self::PRODUCT_IMAGES)];
                ProductMedia::query()->updateOrCreate(
                    ['product_id' => $product->id, 'path' => $imagePath],
                    ['type' => MediaTypeEnum::Image, 'is_primary' => true],
                );

                foreach ($productDefinition['features'] as $feature) {
                    ProductFeature::query()->firstOrCreate(['product_id' => $product->id, 'name' => $feature]);
                }

                return $product;
            })
            ->all();
    }

    /** @param array<string, mixed> $definition
     * @param  array<int, Product>  $products
     */
    private function seedAds(
        int $number,
        array $definition,
        Category $mainCategory,
        array $products,
        AdPackage $adPackage,
        Carbon $firstHomeAdStartsAt,
    ): void {
        $packageDurationDays = (int) $adPackage->duration_days;
        $homeStartsAt = $firstHomeAdStartsAt->copy()->addDays(($number - 1) * $packageDurationDays);

        $placements = [
            [
                'title' => "عرض من {$definition['name']}",
                'caption' => 'اكتشف المنتجات المختارة والعروض الجديدة من متجرنا.',
                'placement' => AdPlacementEnum::Home,
                'action' => AdActionEnum::ViewProduct,
                'product_id' => $products[0]->id,
                'category_id' => null,
                'media_path' => 'ads/U2NyZWVuc2hvdCBmcm9tIDIwMjYtMDktMzAgMjAtNDQtMTQ=_1791636523.webp',
                'starts_at' => $homeStartsAt,
            ],
            [
                'title' => "اختيارات قسم {$definition['category']}",
                'caption' => 'تصفح منتجاتنا المتاحة في هذا القسم.',
                'placement' => AdPlacementEnum::Category,
                'action' => AdActionEnum::ViewProduct,
                'product_id' => $products[1]->id,
                'category_id' => $mainCategory->id,
                'media_path' => 'ads/U2NyZWVuc2hvdCBmcm9tIDIwMjYtMDktMzAgMjAtNDQtMTQ=_1791635865.webp',
                'starts_at' => now()->subDay(),
            ],
            [
                'title' => "تعرّف على {$definition['name']}",
                'caption' => $definition['description'],
                'placement' => AdPlacementEnum::Storefront,
                'action' => AdActionEnum::ViewStore,
                'product_id' => null,
                'category_id' => null,
                'media_path' => self::STORE_COVERS[$number - 1],
                'starts_at' => now()->subDay(),
            ],
        ];

        $store = Store::query()->where('contact_email', "store{$number}@gmail.com")->firstOrFail();

        foreach ($placements as $placement) {
            $title = $placement['title'];
            $ad = Ad::withTrashed()->firstOrNew(['store_id' => $store->id, 'title' => $title]);

            if ($ad->trashed()) {
                $ad->restore();
            }

            $ad->fill([
                'public_id' => $ad->public_id ?? (string) Str::uuid(),
                'ad_package_id' => $adPackage->id,
                'product_id' => $placement['product_id'],
                'category_id' => $placement['category_id'],
                'title' => $title,
                'action_label' => match ($placement['placement']) {
                    AdPlacementEnum::Home => 'تسوق الآن',
                    AdPlacementEnum::Category => 'اكتشف المنتجات',
                    AdPlacementEnum::Storefront => 'زيارة المتجر',
                },
                'caption' => $placement['caption'],
                'placement' => $placement['placement'],
                'action' => $placement['action'],
                'media_type' => MediaTypeEnum::Image,
                'media_path' => $placement['media_path'],
                'status' => $placement['placement'] === AdPlacementEnum::Home && $placement['starts_at']->isFuture()
                    ? AdStatusEnum::Scheduled
                    : AdStatusEnum::Active,
                'cost' => $adPackage->price,
                'currency' => $adPackage->currency,
                'starts_at' => $placement['starts_at'],
                'ends_at' => $placement['starts_at']->copy()->addDays($packageDurationDays),
                'rejection_reason' => null,
            ]);
            $ad->save();
        }
    }

    /** @param array<string, mixed> $definition */
    private function seedCompetition(int $number, array $definition): void
    {
        $store = Store::query()->where('contact_email', "store{$number}@gmail.com")->firstOrFail();
        $title = "مسابقة {$definition['name']}";
        $competition = Competition::withTrashed()->firstOrNew(['store_id' => $store->id, 'title' => $title]);

        if ($competition->trashed()) {
            $competition->restore();
        }

        $answer = (string) $number;
        $competition->fill([
            'public_id' => $competition->public_id ?? (string) Str::uuid(),
            'winner_user_id' => null,
            'content_locale' => 'ar',
            'title' => $title,
            'question' => "ما رقم المتجر في هذه المسابقة؟ اكتب {$number} للمشاركة.",
            'prize' => $definition['prize'],
            'image' => 'competitions/CCuJAHy8jhEseqLPX636cwEz4pyxqVo8gsWADUje.png',
            'status' => CompetitionStatusEnum::Active,
            'correct_answer' => $answer,
            'normalized_correct_answer' => $answer,
            'ends_at' => now()->addDays(30 + $number),
            'drawn_at' => null,
        ]);
        $competition->save();
    }

    private function seedCoupon(int $number): void
    {
        $store = Store::query()->where('contact_email', "store{$number}@gmail.com")->firstOrFail();
        $coupon = Coupon::withTrashed()->firstOrNew([
            'store_id' => $store->id,
            'code' => 'SAEYDEMO'.$number,
        ]);

        if ($coupon->trashed()) {
            $coupon->restore();
        }

        $coupon->fill([
            'discount_type' => CouponDiscountTypeEnum::Percentage,
            'discount_value' => 10,
            'minimum_order_amount' => 200,
            'maximum_discount_amount' => 250,
            'max_uses' => 100,
            'max_uses_per_user' => 1,
            'uses_count' => 0,
            'status' => CouponStatusEnum::Active,
            'starts_at' => now()->subDay(),
            'expires_at' => now()->addDays(30),
        ]);
        $coupon->save();
    }

    /** @return array<int, array{name: string, category: string, description: string, prize: string, products: array<int, array<string, mixed>>}> */
    private function stores(): array
    {
        return [
            [
                'name' => 'لمسة أناقة',
                'category' => 'الأزياء والملابس',
                'description' => 'متجر للأزياء اليومية والقطع العملية المختارة بعناية.',
                'prize' => 'قسيمة شراء بقيمة 500 جنيه',
                'products' => [
                    ['category' => 'ملابس رجالي', 'name' => 'قميص قطني فاخر', 'description' => 'قميص قطني ناعم بقصة مريحة للاستخدام اليومي.', 'price' => 650, 'original_price' => 780, 'stock' => 18, 'features' => ['قطن ناعم', 'مناسب للاستخدام اليومي']],
                    ['category' => 'أحذية', 'name' => 'حذاء يومي مريح', 'description' => 'حذاء خفيف بنعل مرن للمشي طوال اليوم.', 'price' => 920, 'original_price' => null, 'stock' => 12, 'features' => ['نعل مرن', 'خفيف الوزن']],
                    ['category' => 'حقائب', 'name' => 'حقيبة عملية متعددة الاستخدام', 'description' => 'حقيبة واسعة مناسبة للعمل والتنقل اليومي.', 'price' => 740, 'original_price' => 850, 'stock' => 9, 'features' => ['مساحة تخزين واسعة', 'خامة متينة']],
                ],
            ],
            [
                'name' => 'تقنية بلس',
                'category' => 'الإلكترونيات',
                'description' => 'أجهزة إلكترونية وإكسسوارات تقنية مع خيارات تناسب الاستخدام اليومي.',
                'prize' => 'سماعة لاسلكية جديدة',
                'products' => [
                    ['category' => 'هواتف ذكية', 'name' => 'هاتف ذكي بشاشة AMOLED', 'description' => 'هاتف ذكي بذاكرة 256 جيجابايت وشاشة واضحة.', 'price' => 12499, 'original_price' => 13999, 'stock' => 7, 'features' => ['ذاكرة 256 جيجابايت', 'شاشة AMOLED']],
                    ['category' => 'أجهزة كمبيوتر ولابتوب', 'name' => 'لابتوب للأعمال والدراسة', 'description' => 'لابتوب سريع مناسب للمهام اليومية والدراسة.', 'price' => 18999, 'original_price' => null, 'stock' => 5, 'features' => ['قرص SSD سريع', 'مناسب للعمل والدراسة']],
                    ['category' => 'أجهزة صوتية وسماعات', 'name' => 'سماعات لاسلكية بعزل للضوضاء', 'description' => 'سماعات لاسلكية بعلبة شحن وعزل للصوت المحيط.', 'price' => 1450, 'original_price' => 1690, 'stock' => 23, 'features' => ['اتصال Bluetooth', 'علبة شحن مرفقة']],
                ],
            ],
            [
                'name' => 'تفاصيل',
                'category' => 'الإكسسوارات',
                'description' => 'إكسسوارات أنيقة وهدايا عملية تضيف لمسة مميزة لكل يوم.',
                'prize' => 'ساعة يد من تشكيلة المتجر',
                'products' => [
                    ['category' => 'ساعات ونظارات', 'name' => 'ساعة يد بتصميم أنيق', 'description' => 'ساعة عملية بتصميم بسيط يناسب الاستخدام اليومي.', 'price' => 1100, 'original_price' => 1250, 'stock' => 9, 'features' => ['تصميم خفيف', 'سوار قابل للتعديل']],
                    ['category' => 'مجوهرات وحلي', 'name' => 'سوار أنيق بتفاصيل ناعمة', 'description' => 'سوار أنيق مناسب للهدايا والاستخدام اليومي.', 'price' => 540, 'original_price' => null, 'stock' => 16, 'features' => ['تصميم بسيط', 'مناسب للإهداء']],
                    ['category' => 'محافظ وأحزمة', 'name' => 'محفظة جلدية عملية', 'description' => 'محفظة صغيرة بتقسيمات عملية لحفظ البطاقات والنقود.', 'price' => 390, 'original_price' => 450, 'stock' => 21, 'features' => ['تقسيمات متعددة', 'حجم عملي']],
                ],
            ],
            [
                'name' => 'المحرك الذهبي',
                'category' => 'محركات وقطع غيار',
                'description' => 'مستلزمات وقطع غيار تساعدك في صيانة سيارتك والعناية بها.',
                'prize' => 'قسيمة صيانة بقيمة 700 جنيه',
                'products' => [
                    ['category' => 'قطع غيار سيارات', 'name' => 'فلتر زيت عالي الكفاءة', 'description' => 'فلتر زيت مناسب للصيانة الدورية ويحافظ على أداء المحرك.', 'price' => 280, 'original_price' => 320, 'stock' => 14, 'features' => ['للاستخدام في الصيانة الدورية', 'تركيب سهل']],
                    ['category' => 'إطارات وبطاريات', 'name' => 'إطار سيارة متين', 'description' => 'إطار عملي مناسب للقيادة اليومية داخل المدينة.', 'price' => 2150, 'original_price' => null, 'stock' => 8, 'features' => ['تماسك جيد على الطريق', 'مناسب للقيادة اليومية']],
                    ['category' => 'إكسسوارات ومستلزمات سيارات', 'name' => 'منظم مقعد السيارة', 'description' => 'منظم عملي لحفظ الأغراض الصغيرة داخل السيارة.', 'price' => 450, 'original_price' => 520, 'stock' => 11, 'features' => ['جيوب متعددة', 'تركيب سريع']],
                ],
            ],
            [
                'name' => 'زاد البيت',
                'category' => 'الأطعمة والمشروبات',
                'description' => 'منتجات طازجة ومخبوزات ومشروبات مختارة للبيت والعائلة.',
                'prize' => 'سلة منتجات من المتجر',
                'products' => [
                    ['category' => 'مشروبات وعصائر', 'name' => 'عصير مانجو طبيعي', 'description' => 'عصير مانجو بطعم غني، يقدم باردًا.', 'price' => 75, 'original_price' => null, 'stock' => 40, 'features' => ['يقدم باردًا', 'عبوة مناسبة للمشاركة']],
                    ['category' => 'حلويات ومخبوزات', 'name' => 'صندوق مخبوزات مشكلة', 'description' => 'تشكيلة مخبوزات طازجة مناسبة للضيافة.', 'price' => 220, 'original_price' => 250, 'stock' => 16, 'features' => ['تشكيلة متنوعة', 'مناسب للضيافة']],
                    ['category' => 'وجبات خفيفة ومكسرات', 'name' => 'مكسرات مشكلة محمصة', 'description' => 'تشكيلة مكسرات محمصة لوجبة خفيفة خلال اليوم.', 'price' => 180, 'original_price' => null, 'stock' => 26, 'features' => ['تشكيلة متنوعة', 'تحفظ في مكان جاف']],
                ],
            ],
        ];
    }
}
