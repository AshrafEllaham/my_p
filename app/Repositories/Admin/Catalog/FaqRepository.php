<?php

namespace App\Repositories\Admin\Catalog;

use App\Enums\AccountTypeEnum;
use App\Models\Sai\Faq;
use App\Repositories\MainRepository;
use Illuminate\Database\Eloquent\Builder;

class FaqRepository extends MainRepository
{
    public function __construct(Faq $model)
    {
        $this->model = $model;
    }

    public function dataTableQuery(string $locale, ?AccountTypeEnum $type = null): Builder
    {
        return $this->getModel()->newQuery()
            ->leftJoin('faq_translations as faq_translation', function ($join) use ($locale): void {
                $join->on('faq_translation.faq_id', '=', 'faqs.id')
                    ->where('faq_translation.locale', $locale);
            })
            ->select([
                'faqs.id',
                'faqs.type',
                'faqs.created_at',
                'faq_translation.question',
                'faq_translation.answer',
            ])
            ->when($type !== null, fn (Builder $query) => $query->where('faqs.type', $type->value))
            ->orderByDesc('faqs.id');
    }

    public function findForAdmin(int $id): Faq
    {
        return $this->getModel()->newQuery()->with('translations')->findOrFail($id);
    }

    /** @param array<string, mixed> $data */
    public function create(array $data): Faq
    {
        /** @var Faq $faq */
        $faq = $this->store($data);

        return $faq->load('translations');
    }

    /** @param array<string, mixed> $data */
    public function updateFaq(int $id, array $data): Faq
    {
        $faq = $this->findForAdmin($id);
        $faq->fill($data);
        $faq->save();

        return $faq->refresh()->load('translations');
    }

    public function deleteFaq(int $id): void
    {
        $this->delete($id);
    }
}
