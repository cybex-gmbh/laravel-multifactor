<?php

namespace CybexGmbh\LaravelMultiFactor\Enums;

use CybexGmbh\LaravelMultiFactor\Classes\TwoFactorAuthMethodHandler\EmailHandler;
use CybexGmbh\LaravelMultiFactor\Contracts\MultiFactorAuthMethod as TwoFactorAuthMethodContract;
use Illuminate\Support\Facades\Auth;

enum MultiFactorAuthMethod: string
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
        return array_map(fn($value) => self::from($value), config('multi-factor.allowedMethods'));
    }

    public static function getAllowedMethodsNames(): array
    {
        return array_map(fn($method) => $method->value, self::getAllowedMethods());
    }

    public static function getForceMethod(): self
    {
        return self::from(config('multi-factor.forceMethod'));
    }

    public static function getMethodsByNames(array $names): array
    {
        return array_map(fn($name) => self::from($name), $names);
    }

    public static function isEmailOnlyLoginActive(): bool
    {
        return config('multi-factor.routes.email-login.enabled');
    }

    public function isAllowed(): bool
    {
        return in_array($this->value, self::getAllowedMethodsNames());
    }

    public function isUserMethod(): bool
    {
        return in_array($this->value, Auth::user()->getMultiFactorAuthMethodsNames());
    }

    public function getSvg()
    {
        return match ($this) {
            self::EMAIL => 'laravel-multi-factor::svg.email',
        };
    }
}
