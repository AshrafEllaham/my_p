<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class SettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        $imageRules = ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'];

        return [
            'fav_icon' => $imageRules,
            'logo_header' => $imageRules,
            'logo_footer' => $imageRules,
            'whatsapp' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'other_phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255'],
            'facebook' => ['nullable', 'url', 'max:255'],
            'instagram' => ['nullable', 'url', 'max:255'],
            'ad_home_price' => ['required', 'numeric', 'decimal:0,2', 'min:0', 'max:9999999999.99'],
            'ad_category_price' => ['required', 'numeric', 'decimal:0,2', 'min:0', 'max:9999999999.99'],
            'ar' => ['required', 'array:website_name,about_app,privacy,terms_conditions'],
            'ar.website_name' => ['required', 'string', 'max:255'],
            'ar.about_app' => ['required', 'string'],
            'ar.privacy' => ['required', 'string'],
            'ar.terms_conditions' => ['required', 'string'],
            'en' => ['required', 'array:website_name,about_app,privacy,terms_conditions'],
            'en.website_name' => ['required', 'string', 'max:255'],
            'en.about_app' => ['required', 'string'],
            'en.privacy' => ['required', 'string'],
            'en.terms_conditions' => ['required', 'string'],
        ];
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return __('messages.validation.settings.attributes');
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        $messages = [];

        foreach (['fav_icon', 'logo_header', 'logo_footer'] as $field) {
            foreach (['image', 'mimes', 'max', 'uploaded'] as $rule) {
                $messages["{$field}.{$rule}"] = __('messages.validation.settings.image.'.$rule);
            }
        }

        foreach (['whatsapp', 'phone', 'other_phone', 'email', 'facebook', 'instagram'] as $field) {
            foreach (['string', 'max', 'email', 'url'] as $rule) {
                $messages["{$field}.{$rule}"] = __('messages.validation.settings.contact.'.$rule);
            }
        }

        foreach (['ad_home_price', 'ad_category_price'] as $field) {
            foreach (['required', 'numeric', 'decimal', 'min', 'max'] as $rule) {
                $messages["{$field}.{$rule}"] = __('messages.validation.settings.ad_pricing.'.$rule);
            }
        }

        foreach (['ar', 'en'] as $locale) {
            $messages["{$locale}.required"] = __('messages.validation.settings.translations.required');
            $messages["{$locale}.array"] = __('messages.validation.settings.translations.array');
            $messages["{$locale}.website_name.max"] = __('messages.validation.settings.website_name.max');

            foreach (['website_name', 'about_app', 'privacy', 'terms_conditions'] as $field) {
                $messages["{$locale}.{$field}.required"] = __('messages.validation.settings.translations.required');
                $messages["{$locale}.{$field}.string"] = __('messages.validation.settings.translations.string');
            }
        }

        return $messages;
    }
}
