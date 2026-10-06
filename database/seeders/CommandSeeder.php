<?php

namespace Database\Seeders;

use App\Enums\DeveloperCommandEnum;
use App\Models\Admin\Command;
use Illuminate\Database\Seeder;

class CommandSeeder extends Seeder
{
    public function run(): void
    {
        Command::query()->where('command', 'php artisan route:clear')->delete();

        foreach (DeveloperCommandEnum::cases() as $command) {
            Command::query()->firstOrCreate(['command' => $command->value]);
        }
    }
}
