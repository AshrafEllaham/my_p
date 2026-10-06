<?php

namespace App\Enums;

enum DeveloperCommandEnum: string
{
    case About = 'php artisan about';
    case Migrate = 'php artisan migrate';
    case MigrateAndSeed = 'php artisan migrate:fresh --seed';
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
}
