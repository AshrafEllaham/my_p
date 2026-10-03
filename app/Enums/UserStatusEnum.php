<?php

namespace App\Enums;

use App\Enums\Concerns\HasValues;

enum UserStatusEnum: string
{
    use HasValues;

    case PendingVerification = 'pending_verification';
    case Active = 'active';
    case Suspended = 'suspended';
    case DeletionRequested = 'deletion_requested';
    case Rejected = 'rejected';

}
