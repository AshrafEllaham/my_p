<?php

namespace App\Enums;

use App\Enums\Concerns\HasValues;

enum AdInteractionEventTypeEnum: string
{
    use HasValues;

    case Impression = 'impression';
    case Click = 'click';
}
