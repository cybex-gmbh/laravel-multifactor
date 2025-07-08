<?php

namespace Cybex\LaravelMultiFactor\Enums;

use Cybex\LaravelMultiFactor\Classes\MultiFactorAuthMethodHandler\EmailHandler;
use Cybex\LaravelMultiFactor\Contracts\MultiFactorAuthMethodContract;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use MFA;

enum MultiFactorAuthMethod: string
{
    case EMAIL = 'email';
    case TOTP = 'totp';

    public function getHandler(): MultiFactorAuthMethodContract
    {
        return match ($this) {
            self::EMAIL => app(EmailHandler::class),
            self::TOTP => app(EmailHandler::class),
        };
    }

    public static function getAllowedMethods(): array
    {
        return Arr::map(self::getAllowedMethodNames(), fn($value): self => self::from($value));
    }

    public static function getAllowedMethodNames(): array
    {
        return Arr::map(config('multi-factor.allowedMethods'), fn($method): string => Str::lower($method));
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
        return config('multi-factor.features.email-login.enabled');
    }

    public function isAllowed(): bool
    {
        return in_array($this->value, self::getAllowedMethodNames());
    }

    public function isUserMethod(): bool
    {
        return in_array($this->value, MFA::getUser()->getMultiFactorAuthMethodNames());
    }

    public function isForceMethod(): bool
    {
        return $this === self::getForceMethod();
    }
}
