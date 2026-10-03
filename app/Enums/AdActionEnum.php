<?php

namespace App\Enums;

use App\Enums\Concerns\HasValues;

enum AdActionEnum: string
{
    use HasValues;

    case ViewProduct = 'view_product';
    case ViewStore = 'view_store';
    case StartChat = 'start_chat';
    case BuyNow = 'buy_now';
}
