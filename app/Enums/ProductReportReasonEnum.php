<?php

namespace App\Enums;

use App\Enums\Concerns\HasValues;

enum ProductReportReasonEnum: string
{
    use HasValues;

    case IncorrectInformation = 'incorrect_information';
    case Prohibited = 'prohibited';
    case Counterfeit = 'counterfeit';
}
