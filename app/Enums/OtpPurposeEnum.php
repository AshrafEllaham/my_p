<?php

namespace App\Enums;

use App\Enums\Concerns\HasValues;

enum OtpPurposeEnum: string
{
    use HasValues;

    case Registration = 'registration';
    case Login = 'login';
    case PasswordReset = 'password_reset';
    case PhoneChange = 'phone_change';
}
