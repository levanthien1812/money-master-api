<?php

namespace App\Http\Services;

use App\Models\TemporaryVerificationCode;

class VerificationCodeServices
{
    public function createOrUpdate(string $email, string $code): void
    {
        TemporaryVerificationCode::updateOrCreate(
            ['email' => $email],
            ['code' => $code]
        );
    }

    public function checkExists(string $email, string $code): bool
    {
        return TemporaryVerificationCode::where(['email' => $email, 'code' => $code])->exists();
    }
}
