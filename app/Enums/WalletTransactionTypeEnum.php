<?php

namespace App\Enums;

use App\Enums\Concerns\HasValues;

enum WalletTransactionTypeEnum: string
{
    use HasValues;

    case Deposit = 'deposit'; // يخزن إيداع الأموال في المحفظة.
    case Purchase = 'purchase'; // يخزن عملية شراء من المحفظة، حيث يتم خصم المبلغ من الرصيد المتاح.
    case Refund = 'refund'; // يخزن عملية استرداد الأموال إلى المحفظة، حيث يتم إضافة المبلغ إلى الرصيد المتاح.
    case TransferIn = 'transfer_in'; // يخزن عملية تحويل الأموال من محفظة أخرى إلى هذه المحفظة، حيث يتم إضافة المبلغ إلى الرصيد المتاح.
    case TransferOut = 'transfer_out'; // يخزن عملية تحويل الأموال من هذه المحفظة إلى محفظة أخرى، حيث يتم خصم المبلغ من الرصيد المتاح.
    case Withdrawal = 'withdrawal'; // يخزن عملية سحب الأموال من المحفظة، حيث يتم خصم المبلغ من الرصيد المتاح.
    case Earning = 'earning'; // يخزن عملية كسب الأموال في المحفظة، حيث يتم إضافة المبلغ إلى الرصيد المتاح.
    case Commission = 'commission'; // يخزن عملية تحصيل العمولة في المحفظة، حيث يتم إضافة المبلغ إلى الرصيد المتاح.
    case Settlement = 'settlement'; // يخزن عملية تسويه في المحفظة، حيث يتم خصم أو إضافة المبلغ حسب نوع العملية.
    case AdPayment = 'ad_payment'; // يخزن عملية دفع الإعلانات في المحفظة، حيث يتم خصم المبلغ من الرصيد المتاح.
}
