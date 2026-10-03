<?php

namespace App\Enums;

use App\Enums\Concerns\HasValues;

enum MediaTypeEnum: string
{
    use HasValues;

    case Image = 'image';
    case Video = 'video';
    case File = 'file';
}
