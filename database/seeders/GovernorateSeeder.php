<?php

namespace Database\Seeders;

use App\Models\Sai\Country;
use App\Models\Sai\Governorate;
use Illuminate\Database\Seeder;

class GovernorateSeeder extends Seeder
{
    public function run(): void
    {
        $country = Country::where('code', 'EG')->first();

        if (! $country) {
            $country = Country::create([
                'code' => 'EG',
                'phone_code' => '+20',
                'flag' => '🇪🇬',
                'is_active' => true,
                'ar' => ['name' => 'مصر'],
                'en' => ['name' => 'Egypt'],
            ]);
        }

        $governorates = [
            ['ar' => 'القاهرة', 'en' => 'Cairo'],
            ['ar' => 'الجيزة', 'en' => 'Giza'],
            ['ar' => 'الإسكندرية', 'en' => 'Alexandria'],
            ['ar' => 'القليوبية', 'en' => 'Qalyubia'],
            ['ar' => 'الدقهلية', 'en' => 'Dakahlia'],
            ['ar' => 'الشرقية', 'en' => 'Sharqia'],
            ['ar' => 'المنوفية', 'en' => 'Monufia'],
            ['ar' => 'الغربية', 'en' => 'Gharbia'],
            ['ar' => 'البحيرة', 'en' => 'Beheira'],
            ['ar' => 'كفر الشيخ', 'en' => 'Kafr El Sheikh'],
            ['ar' => 'دمياط', 'en' => 'Damietta'],
            ['ar' => 'بورسعيد', 'en' => 'Port Said'],
            ['ar' => 'الإسماعيلية', 'en' => 'Ismailia'],
            ['ar' => 'السويس', 'en' => 'Suez'],
            ['ar' => 'الفيوم', 'en' => 'Fayoum'],
            ['ar' => 'بني سويف', 'en' => 'Beni Suef'],
            ['ar' => 'المنيا', 'en' => 'Minya'],
            ['ar' => 'أسيوط', 'en' => 'Asyut'],
            ['ar' => 'سوهاج', 'en' => 'Sohag'],
            ['ar' => 'قنا', 'en' => 'Qena'],
            ['ar' => 'الأقصر', 'en' => 'Luxor'],
            ['ar' => 'أسوان', 'en' => 'Aswan'],
            ['ar' => 'البحر الأحمر', 'en' => 'Red Sea'],
            ['ar' => 'جنوب سيناء', 'en' => 'South Sinai'],
            ['ar' => 'شمال سيناء', 'en' => 'North Sinai'],
            ['ar' => 'مطروح', 'en' => 'Matrouh'],
            ['ar' => 'الوادي الجديد', 'en' => 'New Valley'],
        ];

        foreach ($governorates as $gov) {
            $existing = Governorate::where('country_id', $country->id)
                ->whereTranslation('name', $gov['ar'], 'ar')
                ->first();

            if (! $existing) {
                Governorate::create([
                    'country_id' => $country->id,
                    'is_active' => true,
                    'ar' => ['name' => $gov['ar']],
                    'en' => ['name' => $gov['en']],
                ]);
            } else {
                $existing->update([
                    'is_active' => true,
                    'ar' => ['name' => $gov['ar']],
                    'en' => ['name' => $gov['en']],
                ]);
            }
        }
    }
}
