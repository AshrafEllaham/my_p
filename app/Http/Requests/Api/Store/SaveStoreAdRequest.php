<?php

namespace App\Http\Requests\Api\Store;

use App\Enums\AdActionEnum;
use App\Enums\AdPlacementEnum;
use App\Enums\AdSubmissionActionEnum;
use App\Enums\MediaTypeEnum;
use App\Enums\PaymentMethodEnum;
use App\Http\Requests\ApiRequest;
use Illuminate\Validation\Rule;

class SaveStoreAdRequest extends ApiRequest
{
    public function rules(): array
    {
        $isCreate = $this->isMethod('post');

        return [
            'ad_package_id' => [$isCreate ? 'required' : 'sometimes', 'integer', Rule::exists('ad_packages', 'id')->where('is_active', true)],
            'product_id' => ['sometimes', 'nullable', 'integer', Rule::exists('products', 'id')],
            'category_id' => ['sometimes', 'nullable', 'integer', Rule::exists('categories', 'id')->where('is_active', true)],
            'title' => [$isCreate ? 'required' : 'sometimes', 'string', 'min:2', 'max:120'],
            'action_label' => ['sometimes', 'nullable', 'string', 'max:60'],
            'caption' => ['sometimes', 'nullable', 'string', 'max:2000'],
            'placement' => [$isCreate ? 'required' : 'sometimes', 'string', Rule::enum(AdPlacementEnum::class)],
            'action' => [$isCreate ? 'required' : 'sometimes', 'string', Rule::enum(AdActionEnum::class)],
            'media_type' => [$isCreate ? 'required' : 'sometimes', 'string', Rule::enum(MediaTypeEnum::class)],
            'media' => [$isCreate ? 'required' : 'sometimes', 'file', 'mimes:jpg,jpeg,png,webp,mp4', 'max:20480'],
            'starts_at' => ['sometimes', 'nullable', 'date', 'after:now'],
            'submission_action' => [$isCreate ? 'required' : 'prohibited', 'string', Rule::enum(AdSubmissionActionEnum::class)],
            'idempotency_key' => [Rule::requiredIf(fn () => $isCreate && $this->input('submission_action') === AdSubmissionActionEnum::SubmitForReview->value), 'nullable', 'uuid'],
            'payment_method' => [
                Rule::requiredIf(fn () => $isCreate && $this->input('submission_action') === AdSubmissionActionEnum::SubmitForReview->value),
                Rule::prohibitedIf(fn () => ! $isCreate || $this->input('submission_action') !== AdSubmissionActionEnum::SubmitForReview->value),
                'string',
                Rule::enum(PaymentMethodEnum::class),
            ],
        ];
    }

    public function attributes(): array
    {
        $labels = [];
        foreach (['ad_package_id', 'product_id', 'category_id', 'title', 'action_label', 'caption', 'placement', 'action', 'media_type', 'media', 'starts_at', 'submission_action', 'idempotency_key', 'payment_method'] as $field) {
            $labels[$field] = __("messages.validation.store_ads.{$field}.label");
        }

        return $labels;
    }

    public function messages(): array
    {
        $messages = [];
        foreach (['ad_package_id', 'product_id', 'category_id', 'title', 'action_label', 'caption', 'placement', 'action', 'media_type', 'media', 'starts_at', 'submission_action', 'idempotency_key', 'payment_method'] as $field) {
            foreach (['required', 'integer', 'exists', 'string', 'min', 'max', 'enum', 'file', 'mimes', 'uploaded', 'date', 'after', 'prohibited'] as $rule) {
                $key = "messages.validation.store_ads.{$field}.{$rule}";
                if (__($key) !== $key) {
                    $messages["{$field}.{$rule}"] = __($key);
                }
            }
        }

        return $messages;
    }
}
