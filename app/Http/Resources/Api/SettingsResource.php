<?php

namespace App\Http\Resources\Api;

use App\Models\Sai\Settings;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Settings */
class SettingsResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        $lang = app()->getLocale();
        $translation = $this->resource->relationLoaded('translations')
            ? $this->resource->getRelation('translations')->firstWhere('locale', app()->getLocale())
            : null;

        return [
            // contact
            'whatsapp' => $this->whatsapp,
            'phone' => $this->phone,
            'other_phone' => $this->other_phone,
            'email' => $this->email,

            // socials
            'facebook' => $this->facebook,
            'instagram' => $this->instagram,

            // ad pricing
            'ad_pricing' => [
                'home' => $this->ad_home_price,
                'category' => $this->ad_category_price,
            ],

            // content
            'privacy' => url($lang.'/privacy'),
            'terms_conditions' => url($lang.'/terms-conditions'),
            'about_app' => url($lang.'/about-app'),
        ];
    }
}
