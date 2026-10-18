<?php

namespace App\Enums;

use App\Enums\Concerns\HasValues;

enum ConversationSafetyActionTypeEnum: string
{
    use HasValues;

    case Report = 'report';
    case Block = 'block';
}
