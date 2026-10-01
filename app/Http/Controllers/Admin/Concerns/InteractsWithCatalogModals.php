<?php

namespace App\Http\Controllers\Admin\Concerns;

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
}
