<?php

namespace CybexGmbh\LaravelMultiFactor\Enums;

enum MultiFactorAuthSession: string
{
    case CODE = 'two_factor_auth_code';
    case EMAIL_SENT = 'two_factor_auth_email_sent';
    case VERIFIED = 'two_factor_auth_verified';
    case SETUP_AFTER_LOGIN = 'two_factor_auth_setup_after_login';

    public static function clear(): void
    {
        session()->forget([
            self::CODE->value,
            self::EMAIL_SENT->value,
        ]);
    }

    public static function isCodeExpired(): bool
    {
        $sessionData = self::CODE->get();

        return now()->greaterThan($sessionData['expires_at']);
    }

    public static function getCode(): ?int
    {
        return self::CODE->get('code');
    }

    public function get(string $key = null): mixed
    {
        return session($this->value)[$key] ?? session($this->value);
    }

    public function put(mixed $value = true): void
    {
        session()->put($this->value, $value);
    }

    public function remove(): void
    {
        session()->remove($this->value);
    }
}
