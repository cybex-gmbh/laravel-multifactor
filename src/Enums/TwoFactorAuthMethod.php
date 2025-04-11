<?php

namespace CybexGmbh\LaravelTwoFactor\Enums;

use CybexGmbh\LaravelTwoFactor\Contracts\TwoFactorAuthMethod as TwoFactorAuthMethodContract;
use Illuminate\Support\Facades\Auth;
use CybexGmbh\LaravelTwoFactor\Classes\TwoFactorAuthMethodHandler\EmailHandler;

enum TwoFactorAuthMethod: string
{
    case EMAIL = 'email';
    case TOTP = 'totp';

    public function getHandler(): TwoFactorAuthMethodContract
    {
        return match ($this) {
            self::EMAIL => app(EmailHandler::class),
        };
    }

    public static function getAllowedMethods(): array
    {
        return array_map(fn($value) => self::from($value), config('two-factor.allowedMethods'));
    }

    public static function getAllowedMethodsNames(): array
    {
        return array_map(fn($method) => $method->value, self::getAllowedMethods());
    }

    public static function getForceMethod(): self
    {
        return self::from(config('two-factor.forceMethod'));
    }

    public function isAllowed(): bool
    {
        return in_array($this->value, self::getAllowedMethodsNames());
    }

    public function isUserMethod(): bool
    {
        return in_array($this->value, Auth::user()->getTwoFactorAuthMethodsNames());
    }

    public function getSvg()
    {
        return match ($this) {
            self::EMAIL => 'laravel-two-factor::svg.email',
        };
    }
}
