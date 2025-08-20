<?php

namespace Cybex\LaravelMultiFactor\Enums;

use Cybex\LaravelMultiFactor\Classes\MultiFactorAuthMethodHandler\EmailHandler;
use Cybex\LaravelMultiFactor\Classes\MultiFactorAuthMethodHandler\TotpHandler;
use Cybex\LaravelMultiFactor\Contracts\MultiFactorAuthMethodContract;
use MFA;

enum MultiFactorAuthMethod: string
{
    case EMAIL = 'email';
    case TOTP = 'totp';

    public function getHandler(): MultiFactorAuthMethodContract
    {
        return match ($this) {
            self::EMAIL => app(EmailHandler::class),
            self::TOTP => app(TotpHandler::class),
        };
    }

    public function isAllowed(): bool
    {
        return in_array($this->value, MFA::getAllowedMethodNames());
    }

    public function isUserMethod(): bool
    {
        return in_array($this->value, MFA::getUser()->getMultiFactorAuthMethodNames());
    }

    public function isForceMethod(): bool
    {
        return $this === MFA::getForceMethod();
    }

    public function doesNeedUserSetup(): bool
    {
        return match ($this) {
            self::EMAIL => false,
            self::TOTP => true,
        };
    }
}
