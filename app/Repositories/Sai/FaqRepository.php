<?php

namespace App\Repositories\Sai;

use App\Enums\AccountTypeEnum;
use App\Models\Sai\Faq;
use App\Repositories\MainRepository;
use Illuminate\Database\Eloquent\Collection;

class FaqRepository extends MainRepository
{
    public function __construct(Faq $model)
    {
        $this->model = $model;
    }

    /** @return Collection<int, Faq> */
    public function getForType(?AccountTypeEnum $type): Collection
    {
        $query = $this->getModel()->newQuery()
            ->select(['id', 'type'])
            ->with('translations')
            ->orderBy('id');

        if ($type !== null) {
            $query->where('type', $type->value);
        }

        /** @var Collection<int, Faq> $faqs */
        $faqs = $query->get();

        return $faqs;
    }
}
