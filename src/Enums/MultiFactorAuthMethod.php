<?php

namespace CybexGmbh\LaravelMultiFactor\Enums;

use CybexGmbh\LaravelMultiFactor\Classes\TwoFactorAuthMethodHandler\EmailHandler;
use CybexGmbh\LaravelMultiFactor\Contracts\MultiFactorAuthMethod as TwoFactorAuthMethodContract;
use Illuminate\Support\Facades\Auth;

enum MultiFactorAuthMethod: string
{
    case EMAIL = 'email';
    case TOTP = 'totp';

    /**
     * @return TwoFactorAuthMethodContract
     */
    public function getHandler(): TwoFactorAuthMethodContract
    {
        return match ($this) {
            self::EMAIL => app(EmailHandler::class),
            self::TOTP => app(EmailHandler::class),
        };
    }

    /**
     * @return array
     */
    public static function getAllowedMethods(): array
    {
        return array_map(fn($value) => self::from($value), config('multi-factor.allowedMethods'));
    }

    /**
     * @return array
     */
    public static function getAllowedMethodsNames(): array
    {
        return array_map(fn($method) => $method->value, self::getAllowedMethods());
    }

    /**
     * @return self
     */
    public static function getForceMethod(): self
    {
        return self::from(config('multi-factor.forceMethod'));
    }

    /**
     * @param array $names
     * @return array
     */
    public static function getMethodsByNames(array $names): array
    {
        return array_map(fn($name) => self::from($name), $names);
    }

    /**
     * @return bool
     */
    public static function isEmailOnlyLoginActive(): bool
    {
        return config('multi-factor.features.email-login.enabled');
    }

    /**
     * @return bool
     */
    public function isAllowed(): bool
    {
        return in_array($this->value, self::getAllowedMethodsNames());
    }

    /**
     * @return bool
     */
    public function isUserMethod(): bool
    {
        return in_array($this->value, Auth::user()->getMultiFactorAuthMethodsNames());
    }

    /**
     * @return bool
     */
    public function isForceMethod(): bool
    {
        return $this === self::getForceMethod();
    }

    /**
     * @return string
     */
    public function getSvg()
    {
        return match ($this) {
            self::EMAIL => 'laravel-multi-factor::svgs.email',
            self::TOTP => 'laravel-multi-factor::svgs.totp',
        };
    }
}
