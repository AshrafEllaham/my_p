<?php

namespace App\Enums;

enum WalletTransactionFilterEnum: string
{
    case All = 'all';
    case Addition = 'addition';
    case Purchase = 'purchase';
    case Refund = 'refund';
    case Transfer = 'transfer';
}
