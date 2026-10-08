<?php

namespace App\Http\Requests\Api;

use App\Http\Requests\ApiRequest;

class GetSettingsRequest extends ApiRequest
{
    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [];
    }
}
