<?php

namespace App\Enums;

use App\Enums\Concerns\HasValues;

enum CompetitionStatusEnum: string
{
    use HasValues;

    case Active = 'active';
    case Ended = 'ended';
    case Drawn = 'drawn';
    case Cancelled = 'cancelled';
}
