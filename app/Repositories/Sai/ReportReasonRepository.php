<?php

namespace App\Repositories\Sai;

use App\Enums\UserReportTypeEnum;
use App\Models\Sai\ReportReason;
use App\Repositories\MainRepository;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class ReportReasonRepository extends MainRepository
{
    public function __construct(ReportReason $model)
    {
        $this->model = $model;
    }

    public function listForUser(string $locale, UserReportTypeEnum $type): Collection
    {
        return $this->getModel()->newQuery()
            ->select(['id', 'type'])
            ->where('type', $type)
            ->with(['translations' => fn ($query) => $query
                ->select(['id', 'report_reason_id', 'locale', 'title'])
                ->where('locale', $locale)])
            ->orderBy('id')
            ->get();
    }

    public function dataTableQuery(string $locale): Builder
    {
        return $this->getModel()->newQuery()
            ->leftJoin('report_reason_translations as reason_translation', function ($join) use ($locale): void {
                $join->on('reason_translation.report_reason_id', '=', 'report_reasons.id')
                    ->where('reason_translation.locale', $locale);
            })
            ->select(['report_reasons.id', 'report_reasons.type', 'reason_translation.title'])
            ->orderByDesc('report_reasons.id');
    }

    public function findForAdmin(int $id): ReportReason
    {
        return $this->getModel()->newQuery()->with('translations')->findOrFail($id);
    }

    /** @param array<string, mixed> $translations */
    public function createWithTranslations(array $translations): ReportReason
    {
        /** @var ReportReason $reason */
        $reason = $this->store($translations);

        return $reason->load('translations');
    }

    /** @param array<string, mixed> $translations */
    public function updateWithTranslations(int $id, array $translations): ReportReason
    {
        $reason = $this->findForAdmin($id);
        $reason->fill($translations);
        $reason->save();

        return $reason->refresh()->load('translations');
    }

    public function hasReports(int $id): bool
    {
        return $this->getModel()->newQuery()->whereKey($id)->whereHas('reports')->exists();
    }

    public function hasEnglishTitle(UserReportTypeEnum $type, string $title): bool
    {
        return $this->getModel()->newQuery()->where('type', $type)->whereHas('translations', fn (Builder $query) => $query
            ->where('locale', 'en')->where('title', $title))->exists();
    }
}
