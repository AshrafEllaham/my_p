<?php

namespace App\Enums;

use App\Enums\Concerns\HasValues;

enum MessageTypeEnum: string
{
    use HasValues;

    case Text = 'text';
    case Image = 'image';
    case File = 'file';
    case System = 'system';
}
