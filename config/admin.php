<?php

use App\Enums\AdminTypeEnum;

return [
    'seed' => [
        'accounts' => [
            'admin' => [
                'name' => env('ADMIN_SEED_NAME', 'مدير النظام'),
                'email' => env('ADMIN_SEED_EMAIL'),
                'phone' => env('ADMIN_SEED_PHONE'),
                'password' => env('ADMIN_SEED_PASSWORD'),
                'type' => AdminTypeEnum::Admin,
            ],
            'developer' => [
                'name' => env('DEVELOPER_SEED_NAME', 'مطور النظام'),
                'email' => env('DEVELOPER_SEED_EMAIL'),
                'phone' => env('DEVELOPER_SEED_PHONE'),
                'password' => env('DEVELOPER_SEED_PASSWORD'),
                'type' => AdminTypeEnum::Developer,
            ],
        ],
    ],
];
