<?php

return [
    'auth' => [
        'registration_created' => 'The account was created successfully.',
        'otp_prepared' => 'The verification code is ready. Use the temporary fixed code 1234.',
        'otp_confirmed' => 'The phone number was verified successfully.',
        'otp_invalid_or_expired' => 'The verification code is invalid or expired.',
        'registration_otp_required' => 'Verify the phone number before creating the account.',
        'login_success' => 'You are now signed in.',
        'social_login_success' => 'You are now signed in with your social account.',
        'credentials_invalid' => 'The sign-in details are incorrect or the account is unavailable.',
        'password_updated' => 'The password was changed successfully.',
        'current_password_invalid' => 'The current password is incorrect.',
        'logout_success' => 'You have been logged out.',
        'social_token_invalid' => 'The social identity could not be verified.',
        'social_account_already_linked' => 'This email is linked to another social sign-in method. Sign in with the linked method first.',
        'account_unavailable' => 'This account is unavailable for sign-in.',
    ],
    'account' => [
        'type_updated' => 'The account type and location were updated successfully.',
        'deleted' => 'The account was deleted successfully.',
    ],
    'banners' => [
        'listed' => 'Banners were retrieved successfully.',
    ],
    'faqs' => [
        'listed' => 'FAQs were retrieved successfully.',
    ],
    'catalog' => [
        'main_categories_listed' => 'Main categories were retrieved successfully.',
        'sub_categories_listed' => 'Subcategories were retrieved successfully.',
        'countries_listed' => 'Countries were retrieved successfully.',
        'governorates_listed' => 'Governorates were retrieved successfully.',
        'cities_listed' => 'Cities were retrieved successfully.',
    ],
    'settings' => [
        'loaded' => 'Application settings were retrieved successfully.',
    ],
    'contact_us' => [
        'created' => 'Your message was sent successfully.',
    ],
    'profile' => [
        'user_updated' => 'The user profile was updated successfully.',
        'store_updated' => 'The store profile was saved successfully.',
        'user_type_required' => 'This endpoint is available to user accounts only.',
        'store_type_required' => 'This endpoint is available to store accounts only.',
    ],
    'notifications' => [
        'listed' => 'Notifications were retrieved successfully.',
        'marked_read' => 'The notification was marked as read.',
        'all_marked_read' => 'All notifications were marked as read.',
        'deleted' => 'All notifications were deleted.',
        'preferences_loaded' => 'Notification preferences were retrieved successfully.',
        'preferences_updated' => 'Notification preferences were updated successfully.',
    ],
    'validation_failed' => 'The submitted data is invalid.',
    'validation' => [
        'change_password' => [
            'current_password_required' => 'The current password is required.',
            'current_password_string' => 'The current password must be text.',
            'password_different' => 'The new password must be different from the current password.',
        ],
        'contact_us' => [
            'attributes' => [
                'name' => 'name',
                'email' => 'email address',
                'subject' => 'subject',
                'message' => 'message',
            ],
            'name' => [
                'required' => 'The name is required.',
                'string' => 'The name must be text.',
                'max' => 'The name must not exceed 150 characters.',
            ],
            'email' => [
                'required' => 'The email address is required.',
                'string' => 'The email address must be text.',
                'email' => 'Enter a valid email address.',
                'max' => 'The email address must not exceed 255 characters.',
            ],
            'subject' => [
                'required' => 'The subject is required.',
                'string' => 'The subject must be text.',
                'max' => 'The subject must not exceed 255 characters.',
            ],
            'message' => [
                'required' => 'The message is required.',
                'string' => 'The message must be text.',
                'min' => 'The message must contain at least 10 characters.',
                'max' => 'The message must not exceed 5,000 characters.',
            ],
        ],
        'developer_command' => [
            'label' => 'Command',
            'required' => 'Enter an Artisan command.',
            'string' => 'The command must be text.',
            'max' => 'The command must not exceed 255 characters.',
            'format' => 'Enter a command in the form php artisan command without shell syntax.',
            'duplicate' => 'This command is already registered.',
            'unavailable' => 'The selected Artisan command is not registered or available.',
        ],
        'phone_code' => [
            'required' => 'The country calling code is required.',
            'string' => 'The country calling code must be a string.',
            'regex' => 'The country calling code is invalid, for example: +20.',
            'max' => 'The country calling code is too long.',
        ],
        'login_identity' => [
            'exclusive' => 'Enter an email address or a phone number, not both.',
        ],
        'social_provider' => [
            'required' => 'The social sign-in provider is required.',
            'enum' => 'The provider must be google or apple.',
        ],
        'social_id_token' => [
            'required' => 'The identity token from Google or Apple is required.',
            'string' => 'The identity token must be a string.',
            'max' => 'The identity token is too long.',
        ],
        'social_id' => [
            'required' => 'The account ID from the provider is required.',
            'string' => 'The social account ID must be a string.',
            'max' => 'The social account ID is too long.',
        ],
        'phone' => [
            'required' => 'The phone number is required.',
            'string' => 'The phone number must be a string.',
            'regex' => 'The phone number is invalid.',
            'unique' => 'The phone number is already registered.',
        ],
        'email' => [
            'required' => 'The email address is required.',
            'string' => 'The email address must be a string.',
            'email' => 'Enter a valid email address.',
            'max' => 'The email address is too long.',
            'unique' => 'The email address is already registered.',
        ],
        'password' => [
            'required' => 'The password is required.',
            'string' => 'The password must be a string.',
            'min' => 'The password must be at least 8 characters.',
            'max' => 'The password is too long.',
            'confirmed' => 'The password confirmation does not match.',
        ],
        'password_confirmation' => [
            'required' => 'The password confirmation is required.',
            'string' => 'The password confirmation must be a string.',
        ],
        'otp' => [
            'required' => 'The verification code is required.',
            'string' => 'The verification code must be a string.',
            'digits' => 'The verification code must contain 4 digits.',
        ],
        'account_type' => [
            'required' => 'The account type is required.',
            'enum' => 'The account type must be user or store.',
        ],
        'banner_type' => [
            'label' => 'The banner audience type',
            'string' => 'The banner audience type must be text.',
            'enum' => 'The banner audience type must be user or store.',
        ],
        'faq_type' => [
            'label' => 'The FAQ audience type',
            'string' => 'The FAQ audience type must be text.',
            'enum' => 'The FAQ audience type must be user or store.',
        ],
        'catalog_lookup' => [
            'pagination' => [
                'label' => 'pagination mode',
                'string' => 'The pagination mode must be text.',
                'in' => 'The pagination mode must be on or off.',
            ],
            'limit_per_page' => [
                'label' => 'items per page',
                'integer' => 'The items per page value must be an integer.',
                'min' => 'The items per page value must be at least 1.',
                'max' => 'The items per page value must not exceed 100.',
            ],
            'main_category_id' => [
                'label' => 'main category ID',
                'integer' => 'The main category ID must be an integer.',
                'exists' => 'The selected active main category was not found.',
            ],
            'country_id' => [
                'label' => 'country ID',
                'integer' => 'The country ID must be an integer.',
                'exists' => 'The selected active country was not found.',
            ],
            'governorate_id' => [
                'label' => 'governorate ID',
                'integer' => 'The governorate ID must be an integer.',
                'exists' => 'The selected active governorate was not found.',
            ],
        ],
        'settings' => [
            'attributes' => [
                'fav_icon' => 'favicon',
                'logo_header' => 'header logo',
                'logo_footer' => 'footer logo',
                'whatsapp' => 'WhatsApp number or URL',
                'phone' => 'phone number',
                'other_phone' => 'other phone number',
                'email' => 'email address',
                'facebook' => 'Facebook URL',
                'instagram' => 'Instagram URL',
                'ar.website_name' => 'website name in Arabic',
                'en.website_name' => 'website name in English',
                'ar.about_app' => 'About the app in Arabic',
                'ar.privacy' => 'Privacy policy in Arabic',
                'ar.terms_conditions' => 'Terms and conditions in Arabic',
                'en.about_app' => 'About the app in English',
                'en.privacy' => 'Privacy policy in English',
                'en.terms_conditions' => 'Terms and conditions in English',
            ],
            'image' => [
                'image' => 'The uploaded file must be an image.',
                'mimes' => 'The image must be a JPG, JPEG, PNG, or WEBP file.',
                'max' => 'The image must not be larger than 5 MB.',
                'uploaded' => 'The image could not be uploaded. Check its size and try again.',
            ],
            'contact' => [
                'string' => 'Contact details must be text.',
                'max' => 'Contact details are too long.',
                'email' => 'Enter a valid email address.',
                'url' => 'Enter a valid URL starting with http or https.',
            ],
            'translations' => [
                'required' => 'This field is required in both Arabic and English.',
                'array' => 'The translation data is invalid.',
                'string' => 'The content must be text.',
            ],
            'website_name' => [
                'max' => 'The website name must not exceed 255 characters.',
            ],
        ],
        'country_id' => [
            'required_with' => 'The country is required when a governorate or city is submitted.',
            'integer' => 'The country ID must be an integer.',
            'exists' => 'The selected country is unavailable.',
        ],
        'governorate_id' => [
            'required_with' => 'The governorate is required when a city is submitted.',
            'integer' => 'The governorate ID must be an integer.',
            'exists' => 'The selected governorate is unavailable or does not belong to the selected country.',
        ],
        'city_id' => [
            'integer' => 'The city ID must be an integer.',
            'exists' => 'The selected city is unavailable or does not belong to the selected governorate.',
        ],
        'profile_name' => [
            'required' => 'The name is required.',
            'string' => 'The name must be a string.',
            'min' => 'The name must contain at least 2 characters.',
            'max' => 'The name is too long.',
        ],
        'profile_image' => [
            'required' => 'The image is required.',
            'image' => 'The uploaded file must be an image.',
            'mimes' => 'The image must be a JPG, JPEG, PNG, or WEBP file.',
            'max' => 'The image must not be larger than 5 MB.',
        ],
        'profile_address' => [
            'string' => 'The address must be a string.',
            'max' => 'The address must not exceed 500 characters.',
        ],
        'category_id' => [
            'required' => 'The store category is required.',
            'integer' => 'The category ID must be an integer.',
            'exists' => 'The selected category is unavailable.',
        ],
        'profile_description' => [
            'string' => 'The store description must be a string.',
            'max' => 'The store description must not exceed 1000 characters.',
        ],
        'profile_cover' => [
            'image' => 'The cover file must be an image.',
            'mimes' => 'The cover must be a JPG, JPEG, PNG, or WEBP file.',
            'max' => 'The cover must not be larger than 10 MB.',
        ],
        'notifications_unread_only' => [
            'boolean' => 'The unread-only filter must be true or false.',
        ],
        'notifications_per_page' => [
            'integer' => 'The page size must be an integer.',
            'min' => 'The page size must be at least 1.',
            'max' => 'The page size must not exceed 100.',
        ],
        'notification_preference' => [
            'required' => 'All notification preferences are required when replacing them.',
            'at_least_one' => 'Choose at least one notification preference to update.',
            'boolean' => 'Every notification preference must be true or false.',
        ],
    ],
];
