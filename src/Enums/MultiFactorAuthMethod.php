<?php

namespace Cybex\LaravelMultiFactor\Enums;

use Cybex\LaravelMultiFactor\Classes\MultiFactorAuthMethodHandler\EmailHandler;
use Cybex\LaravelMultiFactor\Contracts\MultiFactorAuthMethod as MultiFactorAuthMethodContract;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

enum MultiFactorAuthMethod: string
{
    case EMAIL = 'email';

    public function getHandler(): MultiFactorAuthMethodContract
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
        return Arr::map(self::getAllowedMethodNames(), fn($value): self => self::from($value));
    }

    public static function getAllowedMethodNames(): array
    {
        return Arr::map(config('multi-factor.allowedMethods'), fn($method): string => Str::lower($method));
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
        return in_array($this->value, self::getAllowedMethodNames());
    }

    /**
     * @return bool
     */
    public function isUserMethod(): bool
    {
        return in_array($this->value, Auth::user()->getMultiFactorAuthMethodNames());
    }

    /**
     * @return bool
     */
    public function isForceMethod(): bool
    {
        return $this === self::getForceMethod();
    }
}
