<?php

namespace CybexGmbh\LaravelMultiFactor\Enums;

use CybexGmbh\LaravelMultiFactor\Classes\MultiFactorAuthMethodHandler\EmailHandler;
use CybexGmbh\LaravelMultiFactor\Contracts\MultiFactorAuthMethod as TwoFactorAuthMethodContract;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

enum MultiFactorAuthMethod: string
{
    case EMAIL = 'email';

    /**
     * @return TwoFactorAuthMethodContract
     */
    public function getHandler(): TwoFactorAuthMethodContract
    {
        return match ($this) {
            self::EMAIL => app(EmailHandler::class),
        };
    }

    /**
     * @return array
     */
    public static function getAllowedMethods(): array
    {
        return Arr::map(self::getAllowedMethodsNames(), fn($value) => self::from($value));
    }

    /**
     * @return array
     */
    public static function getAllowedMethodsNames(): array
    {
        return Arr::map(config('multi-factor.allowedMethods'), fn($method) => Str::lower($method));
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
}
