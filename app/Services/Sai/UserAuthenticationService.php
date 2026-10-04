<?php

namespace App\Services\Sai;

use App\Contracts\Sai\SocialIdentityVerifier;
use App\Enums\SocialLoginProviderEnum;
use App\Enums\OtpPurposeEnum;
use App\Enums\UserStatusEnum;
use App\Models\Sai\User;
use App\Repositories\Sai\UserRepository;
use App\Repositories\Sai\OneTimePasswordRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class UserAuthenticationService
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly OneTimePasswordRepository $Repository,
        private readonly SocialIdentityVerifier $socialIdentityVerifier,
    ) {}

    /** @param array<string, string|null> $data */
    public function loginWithPassword(array $data): User
    {
        $user = isset($data['email'])
            ? $this->userRepository->findByEmail(mb_strtolower($data['email']))
            : $this->userRepository->findByPhone($data['phone_code'], $data['phone']);

        if ($user === null || $user->status !== UserStatusEnum::Active
            || $user->password === null || ! Hash::check($data['password'], $user->password)) {
            throw ValidationException::withMessages([
                'identity' => __('messages.auth.credentials_invalid'),
            ]);
        }

        $this->userRepository->markLoggedIn($user);

        return $user;
    }

     /** @param array{phone_code: string, phone: string, email: string, password: string, password_confirmation: string} $data */
    public function register(array $data, string $locale): User
    {
        return DB::transaction(function () use ($data, $locale): User {
            $identity = $data['phone_code'].$data['phone'];
            $otp = $this->Repository->findLatestUsable($identity, OtpPurposeEnum::Registration, verified: true);

            if ($otp === null) {
                throw ValidationException::withMessages([
                    'phone' => __('messages.auth.registration_otp_required'),
                ]);
            }

            $user = $this->userRepository->createRecord([
                'email' => $data['email'],
                'phone_code' => $data['phone_code'],
                'phone' => $data['phone'],
                'password' => $data['password'],
                'status' => UserStatusEnum::Active,
                'preferred_locale' => $locale,
            ]);

            $this->Repository->consumeForUser($otp, $user->id);

            return $user;
        });
    }

    /** @param array<string, string|null> $data */
    public function loginBySocial(array $data): User|array
    {
        $user = $this->userRepository->getWhereFirst([
            ['social_id', $data['social_id']]
        ]);

        if (!$user && isset($data['email'])) {
            $user = $this->userRepository->getWhereFirst([
                ['email', $data['email']]
            ]);
        }

        if (!$user) {
            // لو عميل جديد اعمل تسجيل بياناته
            $user = $this->userRepository->createRecord([
                'email' => $data['email'] ?? null,
                'phone_code' => $data['phone_code'] ?? null,
                'phone' => $data['phone'] ?? null,
                'name' => $data['name'] ?? null,
                'social_id' => $data['social_id'],
                'status' => UserStatusEnum::Active,
            ]);
        }


        return $user;
    }
}
