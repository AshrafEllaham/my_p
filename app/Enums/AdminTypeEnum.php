<?php

namespace App\Enums;

enum AdminTypeEnum: int
{
    case Developer = 1;
    case Admin = 2;

    public function label(): string
    {
        return match ($this) {
            self::Developer => __('admin.admin_types.developer'),
            self::Admin => __('admin.admin_types.admin'),
        };
    }
}
