<?php

namespace App\Enums;

use App\Enums\Concerns\HasValues;

enum OrderStatusEnum: string
{
    use HasValues;

    case PendingStore = 'pending_store';
    case Confirmed = 'confirmed';
    case Preparing = 'preparing';
    case ReadyForCompletion = 'ready_for_completion';
    case AwaitingCustomerConfirmation = 'awaiting_customer_confirmation';
    case Completed = 'completed';
    case Cancelled = 'cancelled';
    case Disputed = 'disputed';
    case Refunded = 'refunded';
}
