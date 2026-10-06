<?php

namespace App\Enums;

enum DeveloperCommandEnum: string
{
    case About = 'php artisan about';
    case Migrate = 'php artisan migrate';
    case MigrateAndSeed = 'php artisan migrate --seed';
    case Seed = 'php artisan db:seed';
    case StorageLink = 'php artisan storage:link';
    case CacheClear = 'php artisan cache:clear';
    case ConfigClear = 'php artisan config:clear';
    case RouteCache = 'php artisan route:trans:cache';
    case RouteCacheClear = 'php artisan route:trans:clear';
    case ViewClear = 'php artisan view:clear';
    case EventClear = 'php artisan event:clear';
    case OptimizeClear = 'php artisan optimize:clear';
    case QueueRestart = 'php artisan queue:restart';

    /** @return list<string> */
    public function arguments(): array
    {
        return match ($this) {
            self::About => ['about'],
            self::Migrate => ['migrate'],
            self::MigrateAndSeed => ['migrate', '--seed'],
            self::Seed => ['db:seed'],
            self::StorageLink => ['storage:link'],
            self::CacheClear => ['cache:clear'],
            self::ConfigClear => ['config:clear'],
            self::RouteCache => ['route:trans:cache'],
            self::RouteCacheClear => ['route:trans:clear'],
            self::ViewClear => ['view:clear'],
            self::EventClear => ['event:clear'],
            self::OptimizeClear => ['optimize:clear'],
            self::QueueRestart => ['queue:restart'],
        };
    }
}
