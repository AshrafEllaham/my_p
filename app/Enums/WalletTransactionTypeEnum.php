<?php

namespace App\Enums;

use App\Enums\Concerns\HasValues;

enum WalletTransactionTypeEnum: string
{
    use HasValues;

    case Deposit = 'deposit';
    case Purchase = 'purchase';
    case Refund = 'refund';
    case TransferIn = 'transfer_in';
    case TransferOut = 'transfer_out';
    case Withdrawal = 'withdrawal';
    case Earning = 'earning';
    case Commission = 'commission';
    case Settlement = 'settlement';
    case AdPayment = 'ad_payment';
}
