<?php

namespace App\Http\Requests\Api\Catalog;

class ListCountriesRequest extends LookupListRequest
{
    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return $this->paginationRules();
    }
}
