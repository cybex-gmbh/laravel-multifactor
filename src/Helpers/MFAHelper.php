<?php

namespace CybexGmbh\LaravelMultiFactor\Helpers;

use Illuminate\Support\Carbon;

class MFAHelper
{
    public const CODE = 'two_factor_auth_code';
    public const EMAIL_SENT = 'two_factor_auth_email_sent';
    public const VERIFIED = 'two_factor_auth_verified';
    public const SETUP_AFTER_LOGIN = 'two_factor_auth_setup_after_login';

    public static function clear(): void
    {
        session()->forget([
            self::CODE,
            self::EMAIL_SENT,
        ]);
    }

    public static function setVerified(bool $value = true): void
    {
        self::put(self::VERIFIED, $value);
    }

    public static function setAuthCode(int $code, int $expiresAt): void
    {
        self::put(self::CODE, [
            'code' => $code,
            'expires_at' => $expiresAt,
        ]);
    }

    public static function setEmailSent(bool $value = true): void
    {
        self::put(self::EMAIL_SENT, $value);
    }

    public static function setSetupAfterLogin(bool $value = true): void
    {
        self::put(self::SETUP_AFTER_LOGIN, $value);
    }

    public static function getVerified()
    {
        return self::get(self::VERIFIED);
    }

    public static function getAuthCode()
    {
        return self::get(self::CODE);
    }

    public static function getSetupAfterLogin()
    {
        return self::get(self::SETUP_AFTER_LOGIN);
    }

    public static function isEmailSent(): bool
    {
        return session()->has(self::EMAIL_SENT);
    }

    public static function isCodeExpired(): bool
    {
        $sessionData = self::get(self::CODE);

        return $sessionData && now()->greaterThan(Carbon::createFromTimestamp($sessionData['expires_at']));
    }

    public static function getCode(): ?int
    {
        return self::get(self::CODE, 'code');
    }

    public static function get(string $key, string $subKey = null): mixed
    {
        $data = session($key);
        return $subKey ? ($data[$subKey] ?? null) : $data;
    }

    public static function put(string $key, mixed $value = true): void
    {
        session()->put($key, $value);
    }

    public static function remove(string $key): void
    {
        session()->remove($key);
    }
}