<?php

namespace Database\Seeders;

use App\Enums\AccountTypeEnum;
use App\Enums\ProductStatusEnum;
use App\Enums\UserStatusEnum;
use App\Models\Sai\Category;
use App\Models\Sai\Product;
use App\Models\Sai\User;
use Illuminate\Database\Seeder;
use RuntimeException;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $store = User::query()
            ->where('account_type', AccountTypeEnum::Store)
            ->where('status', UserStatusEnum::Active)
            ->orderBy('id')
            ->first();

        if (! $store) {
            throw new RuntimeException('ProductSeeder requires an active store account.');
        }

        $categories = Category::query()
            ->where('is_active', true)
            ->whereNotNull('parent_id')
            ->with('translations')
            ->get()
            ->keyBy(fn (Category $category): ?string => $category->translate('en')?->name);

        $products = [
            ['sku' => 'SA-TEST-001', 'category' => 'Men Clothing', 'name' => 'قميص قطني كلاسيكي', 'slug' => 'test-cotton-classic-shirt', 'description' => 'قميص قطني مريح للاستخدام اليومي.', 'price' => 650, 'original_price' => 780, 'stock_quantity' => 18],
            ['sku' => 'SA-TEST-002', 'category' => 'Shoes', 'name' => 'حذاء رياضي خفيف', 'slug' => 'test-light-sports-shoes', 'description' => 'حذاء رياضي خفيف ومناسب للمشي.', 'price' => 920, 'original_price' => null, 'stock_quantity' => 12],
            ['sku' => 'SA-TEST-003', 'category' => 'Smartphones', 'name' => 'هاتف ذكي بشاشة AMOLED', 'slug' => 'test-amoled-smartphone', 'description' => 'هاتف ذكي تجريبي بمساحة تخزين 256 جيجابايت.', 'price' => 12499, 'original_price' => 13999, 'stock_quantity' => 7],
            ['sku' => 'SA-TEST-004', 'category' => 'Audio & Headphones', 'name' => 'سماعات لاسلكية', 'slug' => 'test-wireless-headphones', 'description' => 'سماعات لاسلكية بعلبة شحن صغيرة.', 'price' => 1450, 'original_price' => null, 'stock_quantity' => 23],
            ['sku' => 'SA-TEST-005', 'category' => 'Watches & Eyewear', 'name' => 'ساعة يد يومية', 'slug' => 'test-everyday-watch', 'description' => 'ساعة عملية بتصميم بسيط للاستخدام اليومي.', 'price' => 1100, 'original_price' => 1250, 'stock_quantity' => 9],
            ['sku' => 'SA-TEST-006', 'category' => 'Phone Accessories', 'name' => 'شاحن سريع USB-C', 'slug' => 'test-usbc-fast-charger', 'description' => 'شاحن سريع متوافق مع أجهزة USB-C.', 'price' => 390, 'original_price' => null, 'stock_quantity' => 35],
            ['sku' => 'SA-TEST-007', 'category' => 'Car Parts', 'name' => 'فلتر زيت للمحرك', 'slug' => 'test-engine-oil-filter', 'description' => 'فلتر زيت تجريبي مناسب للصيانة الدورية.', 'price' => 280, 'original_price' => 320, 'stock_quantity' => 14],
            ['sku' => 'SA-TEST-008', 'category' => 'Car Accessories', 'name' => 'منظم مقعد السيارة', 'slug' => 'test-car-seat-organizer', 'description' => 'منظم عملي لحفظ أغراض السيارة الصغيرة.', 'price' => 450, 'original_price' => null, 'stock_quantity' => 11],
            ['sku' => 'SA-TEST-009', 'category' => 'Beverages & Juices', 'name' => 'عصير مانجو طبيعي', 'slug' => 'test-natural-mango-juice', 'description' => 'عبوة عصير مانجو مناسبة للتقديم البارد.', 'price' => 75, 'original_price' => null, 'stock_quantity' => 40],
            ['sku' => 'SA-TEST-010', 'category' => 'Sweets & Bakery', 'name' => 'صندوق مخبوزات مشكلة', 'slug' => 'test-mixed-bakery-box', 'description' => 'تشكيلة مخبوزات وحلويات طازجة.', 'price' => 220, 'original_price' => 250, 'stock_quantity' => 16],
        ];

        foreach ($products as $index => $product) {
            $category = $categories->get($product['category']);

            if (! $category) {
                throw new RuntimeException("ProductSeeder could not find active category: {$product['category']}");
            }

            $originalPrice = $product['original_price'];
            Product::query()->updateOrCreate(
                ['store_id' => $store->id, 'sku' => $product['sku']],
                [
                    'category_id' => $category->id,
                    'name' => $product['name'],
                    'description' => $product['description'],
                    'slug' => $product['slug'],
                    'status' => ProductStatusEnum::Published,
                    'price' => $product['price'],
                    'original_price' => $originalPrice,
                    'discount_percentage' => $originalPrice ? round((1 - ($product['price'] / $originalPrice)) * 100, 2) : null,
                    'discount_ends_at' => $originalPrice ? now()->addDays(30) : null,
                    'stock_quantity' => $product['stock_quantity'],
                    'low_stock_threshold' => 5,
                    'is_featured' => $index === 0,
                    'published_at' => now()->subDays($index),
                ],
            );
        }
    }
}
