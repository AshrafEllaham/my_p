<?php

namespace App\Http\Requests\Api;

use App\Http\Requests\ApiRequest;

class ContactUsRequest extends ApiRequest
{
    /** @return array<string, array<int, string>> */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'string', 'email:rfc', 'max:255'],
            'subject' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string', 'min:10', 'max:5000'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        $fields = ['name', 'email', 'subject', 'message'];
        $rules = ['required', 'string', 'email', 'min', 'max'];
        $messages = [];

        foreach ($fields as $field) {
            foreach ($rules as $rule) {
                $key = "messages.validation.contact_us.{$field}.{$rule}";
                if (__($key) !== $key) {
                    $messages["{$field}.{$rule}"] = __($key);
                }
            }
        }

        return $messages;
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return [
            'name' => __('messages.validation.contact_us.attributes.name'),
            'email' => __('messages.validation.contact_us.attributes.email'),
            'subject' => __('messages.validation.contact_us.attributes.subject'),
            'message' => __('messages.validation.contact_us.attributes.message'),
        ];
    }
}
