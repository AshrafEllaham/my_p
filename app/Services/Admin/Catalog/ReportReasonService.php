<?php

namespace App\Services\Admin\Catalog;

use App\Models\Sai\ReportReason;
use App\Repositories\Sai\ReportReasonRepository;
use Illuminate\Database\DatabaseManager;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\ValidationException;

class ReportReasonService
{
    public function __construct(
        private readonly ReportReasonRepository $reasons,
        private readonly DatabaseManager $database,
    ) {}

    public function dataTableQuery(string $locale): Builder
    {
        return $this->reasons->dataTableQuery($locale);
    }

    public function find(int $id): ReportReason
    {
        return $this->reasons->findForAdmin($id);
    }

    /** @param array<string, mixed> $data */
    public function create(array $data): ReportReason
    {
        return $this->database->transaction(fn (): ReportReason => $this->reasons->createWithTranslations($data));
    }

    /** @param array<string, mixed> $data */
    public function update(int $id, array $data): ReportReason
    {
        return $this->database->transaction(fn (): ReportReason => $this->reasons->updateWithTranslations($id, $data));
    }

    public function delete(int $id): void
    {
        $this->database->transaction(function () use ($id): void {
            $this->reasons->findForAdmin($id);
            if ($this->reasons->hasReports($id)) {
                throw ValidationException::withMessages(['delete' => __('admin.report_reasons.in_use')]);
            }

            $this->reasons->delete($id);
        });
    }
}
