<?php

namespace App\Enums;

use App\Enums\Concerns\HasValues;

enum AdSubmissionActionEnum: string
{
    use HasValues;

    case SaveAsDraft = 'save_as_draft';
    case SubmitForReview = 'submit_for_review';
}
