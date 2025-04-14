<?php

namespace CybexGmbh\LaravelMultiFactor\Enums;

enum MultiFactorAuthSession: string
{
    case CODE = 'two_factor_auth_code';
    case EMAIL_SENT = 'two_factor_auth_email_sent';
    case VERIFIED = 'two_factor_auth_verified';

    public static function clear(): void
    {
        session()->forget([
            self::CODE->value,
            self::EMAIL_SENT->value,
        ]);
    }

    public function get(): mixed
    {
        return session($this->value);
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
