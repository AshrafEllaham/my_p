<?php

namespace App\Enums;

use App\Enums\Concerns\HasValues;

enum StoreDocumentTypeEnum: string
{
    use HasValues;

    case BusinessProof = 'business_proof';
    case ReplacementBusinessProof = 'replacement_business_proof';
    case IdentityProof = 'identity_proof';
}
