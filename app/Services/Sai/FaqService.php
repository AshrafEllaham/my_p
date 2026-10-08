<?php

namespace App\Services\Sai;

use App\Enums\AccountTypeEnum;
use App\Models\Sai\Faq;
use App\Repositories\Sai\FaqRepository;
use Illuminate\Database\Eloquent\Collection;

class FaqService
{
    public function __construct(private readonly FaqRepository $repository) {}

    /** @return Collection<int, Faq> */
    public function list(?AccountTypeEnum $type): Collection
    {
        return $this->repository->getForType($type);
    }
}
