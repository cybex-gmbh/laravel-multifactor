<?php

namespace Cybex\LaravelMultiFactor\Enums;

use Cybex\LaravelMultiFactor\Classes\MultiFactorAuthMethodHandler\EmailHandler;
use Cybex\LaravelMultiFactor\Contracts\MultiFactorAuthMethodContract;
use Illuminate\Support\Facades\Auth;
use MFA;

enum MultiFactorAuthMethod: string
{
    case EMAIL = 'email';

    public function getHandler(): MultiFactorAuthMethodContract
    {
        return match ($this) {
            self::EMAIL => app(EmailHandler::class),
        };
    }

    public function isAllowed(): bool
    {
        return in_array($this->value, MFA::getAllowedMethodNames());
    }

    public function isUserMethod(): bool
    {
        return in_array($this->value, Auth::user()->getMultiFactorAuthMethodNames());
    }

    public function isForceMethod(): bool
    {
        return $this === MFA::getForceMethod();
    }
}
