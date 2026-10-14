<?php

namespace App\Enums;

use App\Enums\Concerns\HasValues;

enum UserCompetitionStatusFilterEnum: string
{
    use HasValues;

    case AvailableNow = 'available_now';
    case Ended = 'ended';
}
