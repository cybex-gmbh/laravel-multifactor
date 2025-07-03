<?php

namespace Cybex\LaravelMultiFactor\Helpers;

use Illuminate\Support\Carbon;

class MFAHelper
{
    public const CODE = 'two_factor_auth_code';
    public const EMAIL_SENT = 'two_factor_auth_email_sent';
    public const VERIFIED = 'two_factor_auth_verified';
    public const SETUP_AFTER_LOGIN = 'two_factor_auth_setup_after_login';

    public function clear(): void
    {
        session()->forget([
            self::CODE,
            self::EMAIL_SENT,
            self::VERIFIED,
            self::SETUP_AFTER_LOGIN
        ]);
    }

    public function setVerified(bool $value = true): void
    {
        $this->put(self::VERIFIED, $value);
    }

    public function setAuthCode(int $code, int $expiresAt): void
    {
        $this->put(self::CODE, [
            'code' => $code,
            'expires_at' => $expiresAt,
        ]);
    }

    public function setEmailSent(bool $value = true): void
    {
        $this->put(self::EMAIL_SENT, $value);
    }

    public function setSetupAfterLogin(bool $value = true): void
    {
        $this->put(self::SETUP_AFTER_LOGIN, $value);
    }

    public function isVerified(): bool
    {
        return filled($this->get(self::VERIFIED));
    }

    public function getAuthCode()
    {
        return $this->get(self::CODE);
    }

    public function isInSetupAfterLogin()
    {
        return $this->get(self::SETUP_AFTER_LOGIN);
    }

    public function endSetupAfterLogin()
    {
        $this->remove(self::SETUP_AFTER_LOGIN);
    }

    public function isEmailSent(): bool
    {
        return session()->has(self::EMAIL_SENT);
    }

    public function isCodeExpired(): bool
    {
        $sessionData = $this->get(self::CODE);

        return $sessionData && now()->greaterThan(Carbon::createFromTimestamp($sessionData['expires_at']));
    }

    public function getCode(): ?int
    {
        return $this->get(self::CODE, 'code');
    }

    protected function get(string $key, string $subKey = null): mixed
    {
        $data = session($key);
        return $subKey ? ($data[$subKey] ?? null) : $data;
    }

    protected function put(string $key, mixed $value = true): void
    {
        session()->put($key, $value);
    }

    public function remove(string $key): void
    {
        session()->remove($key);
    }
}