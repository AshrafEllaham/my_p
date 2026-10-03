<?php

namespace App\Enums;

use App\Enums\Concerns\HasValues;

enum InventoryMovementReasonEnum: string
{
    use HasValues;

    case Initial = 'initial';
    case ManualAdjustment = 'manual_adjustment';
    case OrderReserved = 'order_reserved';
    case OrderCancelled = 'order_cancelled';
    case SaleCompleted = 'sale_completed';
    case ReturnRestocked = 'return_restocked';
}
