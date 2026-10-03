<?php

namespace App\Enums;

use App\Enums\Concerns\HasValues;

enum PayoutMethodEnum: string
{
    use HasValues;

    case BankAccount = 'bank_account';
    case MobileWallet = 'mobile_wallet';
}
