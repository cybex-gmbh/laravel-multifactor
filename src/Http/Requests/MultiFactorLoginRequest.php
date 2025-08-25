<?php

namespace Cybex\LaravelMultiFactor\Http\Requests;

use Cybex\LaravelMultiFactor\Enums\MultiFactorAuthMethod;
use Laravel\Fortify\Http\Requests\TwoFactorLoginRequest;
use MFA;

class MultiFactorLoginRequest extends TwoFactorLoginRequest
{
    public function hasValidMFACode($method): bool
    {
        if ($method === MultiFactorAuthMethod::TOTP) {
            return $this->hasValidCode();
        }

        return $this->verifyOneTimePassword();
    }

    protected function verifyOneTimePassword(): bool
    {
        if (MFA::isCodeExpired() || MFA::getCode() !== (int) $this->code) {
            return false;
        }

        return true;
    }
}
