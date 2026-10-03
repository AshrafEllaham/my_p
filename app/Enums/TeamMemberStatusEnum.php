<?php

namespace App\Enums;

use App\Enums\Concerns\HasValues;

enum TeamMemberStatusEnum: string
{
    use HasValues;

    case Invited = 'invited';
    case Active = 'active';
    case Suspended = 'suspended';
    case Revoked = 'revoked';
}
