<?php

namespace App\Http\Controllers\Admin\Concerns;

use App\Enums\AccountTypeEnum;
use Illuminate\Http\Request;

trait InteractsWithCatalogModals
{
    /** @param array<string, mixed> $data */
    protected function modal(string $view, array $data = []): never
    {
        $this->dashBoardJson(200, null, [
            'html' => view($view, $data)->render(),
        ]);
    }

    protected function catalogSuccess(string $message): never
    {
        $this->dashBoardJson(200, $message);
    }

    /** @return array{editUrl: string, showUrl: string, deleteUrl: string} */
    protected function actionUrls(string $resource, int $id): array
    {
        return [
            'editUrl' => route("admin.{$resource}.edit", $id),
            'showUrl' => route("admin.{$resource}.show", $id),
            'deleteUrl' => route("admin.{$resource}.destroy", $id),
        ];
    }

    /**
     * @return array<int, array{name: string, label: string, all_label: string, value: string, options: array<string, string>}>
     */
    protected function accountTypeFilter(Request $request): array
    {
        return [
            [
                'name' => 'type',
                'label' => __('admin.catalog.filters.account_type'),
                'all_label' => __('admin.catalog.filters.all_types'),
                'value' => (string) $request->query('type', ''),
                'options' => [
                    AccountTypeEnum::User->value => __('admin.catalog.account_types.user'),
                    AccountTypeEnum::Store->value => __('admin.catalog.account_types.store'),
                ],
            ],
        ];
    }
}
