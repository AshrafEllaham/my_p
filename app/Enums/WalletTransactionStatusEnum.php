<?php

namespace App\Enums;

use App\Enums\Concerns\HasValues;

enum WalletTransactionStatusEnum: string
{
    use HasValues;

    case Pending = 'pending';
    case Completed = 'completed';
    case Failed = 'failed';
    case Reversed = 'reversed';
}
