<?php

namespace Database\Seeders;

use App\Models\Twenty\Country;
use Illuminate\Database\Seeder;

class CountrySeeder extends Seeder
{
    public function run(): void
    {
        Country::updateOrCreate(
            ['code' => 'EG'],
            [
                'phone_code' => '+20',
                'flag' => '🇪🇬',
                'is_active' => true,
                'ar' => ['name' => 'مصر'],
                'en' => ['name' => 'Egypt'],
            ]
        );
    }
}
