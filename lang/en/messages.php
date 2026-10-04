<?php

return [
    'auth' => [
        'registration_created' => 'The account was created successfully.',
        'otp_prepared' => 'The verification code is ready. Use the temporary fixed code 1234.',
        'otp_confirmed' => 'The phone number was verified successfully.',
        'otp_invalid_or_expired' => 'The verification code is invalid or expired.',
        'registration_otp_required' => 'Verify the phone number before creating the account.',
    ],
    'account' => [
        'type_updated' => 'The account type and location were updated successfully.',
    ],
    'profile' => [
        'user_updated' => 'The user profile was updated successfully.',
        'store_updated' => 'The store profile was saved successfully.',
        'user_type_required' => 'This endpoint is available to user accounts only.',
        'store_type_required' => 'This endpoint is available to store accounts only.',
    ],
    'validation_failed' => 'The submitted data is invalid.',
    'validation' => [
        'phone_code' => [
            'required' => 'The country calling code is required.',
            'string' => 'The country calling code must be a string.',
            'regex' => 'The country calling code is invalid, for example: +20.',
            'max' => 'The country calling code is too long.',
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
    ],
];
