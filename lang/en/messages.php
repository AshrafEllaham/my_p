<?php

return [
    'auth' => [
        'registration_created' => 'The account was created successfully.',
        'otp_prepared' => 'The verification code is ready. Use the temporary fixed code 1234.',
        'otp_confirmed' => 'The phone number was verified successfully.',
        'otp_invalid_or_expired' => 'The verification code is invalid or expired.',
        'registration_otp_required' => 'Verify the phone number before creating the account.',
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
    ],
];
