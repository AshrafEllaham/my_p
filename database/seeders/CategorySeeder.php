<?php

namespace Database\Seeders;

use App\Models\Twenty\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'slug' => 'fashion',
                'icon' => 'shirt',
                'sort_order' => 1,
                'ar' => [
                    'name' => 'الأزياء والملابس',
                    'description' => 'كل ما يخص الأزياء والملابس الرجالية والنسائية والأطفال',
                ],
                'en' => [
                    'name' => 'Fashion & Clothing',
                    'description' => 'Everything related to fashion, men, women, and kids apparel',
                ],
                'sub' => [
                    [
                        'slug' => 'mens-clothing',
                        'ar' => ['name' => 'ملابس رجالي', 'description' => 'أحدث تشكيلات الملابس الرجالية'],
                        'en' => ['name' => 'Men Clothing', 'description' => 'Latest men fashion collections'],
                    ],
                    [
                        'slug' => 'womens-clothing',
                        'ar' => ['name' => 'ملابس حريمي', 'description' => 'أزياء وملابس نسائية عصرية'],
                        'en' => ['name' => 'Women Clothing', 'description' => 'Modern women apparel and fashion'],
                    ],
                    [
                        'slug' => 'kids-clothing',
                        'ar' => ['name' => 'ملابس أطفال', 'description' => 'ملابس أطفال بمختلف الأعمار'],
                        'en' => ['name' => 'Kids Clothing', 'description' => 'Kids clothing for all ages'],
                    ],
                    [
                        'slug' => 'shoes',
                        'ar' => ['name' => 'أحذية', 'description' => 'أحذية رياضية ورسمية مريحة'],
                        'en' => ['name' => 'Shoes', 'description' => 'Comfortable sports and formal shoes'],
                    ],
                    [
                        'slug' => 'bags',
                        'ar' => ['name' => 'حقائب', 'description' => 'حقائب يد وظهر بمختلف المقاسات'],
                        'en' => ['name' => 'Bags', 'description' => 'Handbags, backpacks, and luggage'],
                    ],
                ],
            ],
            [
                'slug' => 'electronics',
                'icon' => 'smartphone',
                'sort_order' => 2,
                'ar' => [
                    'name' => 'الإلكترونيات',
                    'description' => 'الأجهزة الذكية والإلكترونيات الاستهلاكية الحديثة',
                ],
                'en' => [
                    'name' => 'Electronics',
                    'description' => 'Smart devices and modern consumer electronics',
                ],
                'sub' => [
                    [
                        'slug' => 'smartphones',
                        'ar' => ['name' => 'هواتف ذكية', 'description' => 'أحدث الهواتف الذكية والأجهزة اللوحية'],
                        'en' => ['name' => 'Smartphones', 'description' => 'Latest smartphones and tablets'],
                    ],
                    [
                        'slug' => 'computers-laptops',
                        'ar' => ['name' => 'أجهزة كمبيوتر ولابتوب', 'description' => 'أجهزة حاسوب محمولة ومكتبية وملحقاتها'],
                        'en' => ['name' => 'Computers & Laptops', 'description' => 'Laptops, desktop PCs, and computer accessories'],
                    ],
                    [
                        'slug' => 'screens-tvs',
                        'ar' => ['name' => 'شاشات وتلفزيونات', 'description' => 'شاشات ذكية بدقة عالية وتلفزيونات منزلية'],
                        'en' => ['name' => 'Screens & TVs', 'description' => 'Smart 4K TVs and high resolution monitors'],
                    ],
                    [
                        'slug' => 'home-appliances',
                        'ar' => ['name' => 'أجهزة منزلية كهربائية', 'description' => 'أجهزة منزلية كهربائية ومطبخية'],
                        'en' => ['name' => 'Home Appliances', 'description' => 'Electrical home and kitchen appliances'],
                    ],
                    [
                        'slug' => 'audio-headphones',
                        'ar' => ['name' => 'أجهزة صوتية وسماعات', 'description' => 'سماعات رأس ومكبرات صوت لاسلكية'],
                        'en' => ['name' => 'Audio & Headphones', 'description' => 'Wireless speakers and headphones'],
                    ],
                ],
            ],
            [
                'slug' => 'accessories',
                'icon' => 'watch',
                'sort_order' => 3,
                'ar' => [
                    'name' => 'الإكسسوارات',
                    'description' => 'إكسسوارات شخصية ومستلزمات أناقة وأجهزة',
                ],
                'en' => [
                    'name' => 'Accessories',
                    'description' => 'Personal styling accessories and gadget essentials',
                ],
                'sub' => [
                    [
                        'slug' => 'watches-eyewear',
                        'ar' => ['name' => 'ساعات ونظارات', 'description' => 'ساعات يد ونظارات شمسية وطبية'],
                        'en' => ['name' => 'Watches & Eyewear', 'description' => 'Wristwatches, sunglasses, and frames'],
                    ],
                    [
                        'slug' => 'jewelry',
                        'ar' => ['name' => 'مجوهرات وحلي', 'description' => 'مجوهرات وإكسسوارات زينة راقية'],
                        'en' => ['name' => 'Jewelry', 'description' => 'Fine and fashion jewelry'],
                    ],
                    [
                        'slug' => 'phone-accessories',
                        'ar' => ['name' => 'إكسسوارات هواتف', 'description' => 'جرابات وشواحن وكابلات وحوامل'],
                        'en' => ['name' => 'Phone Accessories', 'description' => 'Cases, chargers, cables, and stands'],
                    ],
                    [
                        'slug' => 'wallets-belts',
                        'ar' => ['name' => 'محافظ وأحزمة', 'description' => 'محافظ جلدية وأحزمة كلاسيكية وعصرية'],
                        'en' => ['name' => 'Wallets & Belts', 'description' => 'Leather wallets and stylish belts'],
                    ],
                ],
            ],
            [
                'slug' => 'motors',
                'icon' => 'car',
                'sort_order' => 4,
                'ar' => [
                    'name' => 'محركات وقطع غيار',
                    'description' => 'قطع غيار سيارات ومستلزمات صيانة ومعدات ميكانيكية',
                ],
                'en' => [
                    'name' => 'Motors & Auto Parts',
                    'description' => 'Auto spare parts, maintenance supplies, and mechanical tools',
                ],
                'sub' => [
                    [
                        'slug' => 'car-parts',
                        'ar' => ['name' => 'قطع غيار سيارات', 'description' => 'قطع غيار أصلية وموثوقة لجميع السيارات'],
                        'en' => ['name' => 'Car Parts', 'description' => 'Original and aftermarket auto parts'],
                    ],
                    [
                        'slug' => 'tires-batteries',
                        'ar' => ['name' => 'إطارات وبطاريات', 'description' => 'إطارات سيارات وبطاريات عالية الكفاءة'],
                        'en' => ['name' => 'Tires & Batteries', 'description' => 'Car tires and heavy duty batteries'],
                    ],
                    [
                        'slug' => 'motor-oils',
                        'ar' => ['name' => 'زيوت وسوائل محركات', 'description' => 'زيوت محركات وسوائل تبريد وتشحيم'],
                        'en' => ['name' => 'Motor Oils & Fluids', 'description' => 'Engine oils, coolants, and lubricants'],
                    ],
                    [
                        'slug' => 'car-accessories',
                        'ar' => ['name' => 'إكسسوارات ومستلزمات سيارات', 'description' => 'كماليات وفرش ومستلزمات سيارات داخلية وخارجية'],
                        'en' => ['name' => 'Car Accessories', 'description' => 'Interior and exterior auto accessories'],
                    ],
                    [
                        'slug' => 'motorcycles-equipment',
                        'ar' => ['name' => 'دراجات نارية ومعدات', 'description' => 'مستلزمات الدراجات النارية والورش'],
                        'en' => ['name' => 'Motorcycles & Equipment', 'description' => 'Motorcycles gear and garage equipment'],
                    ],
                ],
            ],
            [
                'slug' => 'food-beverages',
                'icon' => 'coffee',
                'sort_order' => 5,
                'ar' => [
                    'name' => 'الأطعمة والمشروبات',
                    'description' => 'منتجات غذائية ومشروبات ومعلبات طازجة ومختارة',
                ],
                'en' => [
                    'name' => 'Food & Beverages',
                    'description' => 'Grocery, food products, fresh drinks, and pantry essentials'],
                'sub' => [
                    [
                        'slug' => 'beverages-juices',
                        'ar' => ['name' => 'مشروبات وعصائر', 'description' => 'مشروبات باردة وساخنة وعصائر طبيعية'],
                        'en' => ['name' => 'Beverages & Juices', 'description' => 'Cold and hot drinks and natural juices'],
                    ],
                    [
                        'slug' => 'grocery-pantry',
                        'ar' => ['name' => 'بقالة ومواد تموينية', 'description' => 'سلع أساسية ومواد تموينية يومية'],
                        'en' => ['name' => 'Grocery & Pantry', 'description' => 'Staples and everyday cooking ingredients'],
                    ],
                    [
                        'slug' => 'sweets-bakery',
                        'ar' => ['name' => 'حلويات ومخبوزات', 'description' => 'حلويات وشوكولاتة ومخبوزات طازجة'],
                        'en' => ['name' => 'Sweets & Bakery', 'description' => 'Pastries, bakery, and chocolates'],
                    ],
                    [
                        'slug' => 'dairy-products',
                        'ar' => ['name' => 'منتجات ألبان', 'description' => 'أجبان وحليب ومنتجات ألبان متنوعة'],
                        'en' => ['name' => 'Dairy Products', 'description' => 'Cheeses, milk, and dairy items'],
                    ],
                    [
                        'slug' => 'snacks-nuts',
                        'ar' => ['name' => 'وجبات خفيفة ومكسرات', 'description' => 'تسالي ومكسرات ومقرمشات مختارة'],
                        'en' => ['name' => 'Snacks & Nuts', 'description' => 'Selected nuts, chips, and snacks'],
                    ],
                ],
            ],
        ];

        foreach ($categories as $catData) {
            $parent = Category::where('slug', $catData['slug'])->first();

            if (! $parent) {
                $parent = Category::create([
                    'parent_id' => null,
                    'slug' => $catData['slug'],
                    'icon' => $catData['icon'],
                    'sort_order' => $catData['sort_order'],
                    'is_active' => true,
                    'ar' => $catData['ar'],
                    'en' => $catData['en'],
                ]);
            } else {
                $parent->update([
                    'icon' => $catData['icon'],
                    'sort_order' => $catData['sort_order'],
                    'is_active' => true,
                    'ar' => $catData['ar'],
                    'en' => $catData['en'],
                ]);
            }

            if (isset($catData['sub'])) {
                foreach ($catData['sub'] as $index => $subData) {
                    $sub = Category::where('slug', $subData['slug'])->first();

                    if (! $sub) {
                        Category::create([
                            'parent_id' => $parent->id,
                            'slug' => $subData['slug'],
                            'sort_order' => $index + 1,
                            'is_active' => true,
                            'ar' => $subData['ar'],
                            'en' => $subData['en'],
                        ]);
                    } else {
                        $sub->update([
                            'parent_id' => $parent->id,
                            'sort_order' => $index + 1,
                            'is_active' => true,
                            'ar' => $subData['ar'],
                            'en' => $subData['en'],
                        ]);
                    }
                }
            }
        }
    }
}
