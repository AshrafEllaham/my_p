<?php

namespace App\Enums;

use App\Enums\Concerns\HasValues;

enum GeneratedDocumentTypeEnum: string
{
    use HasValues;

    case OrderInvoice = 'order_invoice';
    case SettlementStatement = 'settlement_statement';
    case MerchantReport = 'merchant_report';
    case AccountDataExport = 'account_data_export';
}
