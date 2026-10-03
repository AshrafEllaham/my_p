<?php

namespace App\Enums;

use App\Enums\Concerns\HasValues;

enum OrderStatusEnum: string
{
    use HasValues;

    case PendingStore = 'pending_store'; // يخزن حالة الطلب عندما يكون في انتظار تأكيد المتجر أو معالجته.
    case Confirmed = 'confirmed'; // يخزن حالة الطلب عندما يكون قد تم تأكيده من قبل المتجر ويبدأ في التحضير.
    case Preparing = 'preparing'; // يخزن حالة الطلب عندما يكون المتجر في مرحلة التحضير للطلب.
    case ReadyForCompletion = 'ready_for_completion';  // يخزن حالة الطلب عندما يكون جاهزًا لاستلامه من قبل العميل أو التسليم، ولكن لم يتم تأكيد استلامه بعد.
    case AwaitingCustomerConfirmation = 'awaiting_customer_confirmation'; // يخزن حالة الطلب عندما يكون في انتظار تأكيد العميل.
    case Completed = 'completed';
    case Cancelled = 'cancelled';
    case Disputed = 'disputed';
    case Refunded = 'refunded';
}
