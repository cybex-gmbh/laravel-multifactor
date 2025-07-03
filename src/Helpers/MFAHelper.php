<?php

namespace Cybex\LaravelMultiFactor\Helpers;

use Cybex\LaravelMultiFactor\Enums\MultiFactorAuthMethod;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

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
        $this->putInSession(self::VERIFIED, $value);
    }

    public function setAuthCode(int $code, int $expiresAt): void
    {
        $this->putInSession(self::CODE, [
            'code' => $code,
            'expires_at' => $expiresAt,
        ]);
    }

    public function setEmailSent(bool $value = true): void
    {
        $this->putInSession(self::EMAIL_SENT, $value);
    }

    public function setSetupAfterLogin(bool $value = true): void
    {
        $this->putInSession(self::SETUP_AFTER_LOGIN, $value);
    }

    public function isVerified(): bool
    {
        return filled($this->getFromSession(self::VERIFIED));
    }

    public function getAuthCode()
    {
        return $this->getFromSession(self::CODE);
    }

    public function getForceMethod(): MultiFactorAuthMethod
    {
        return MultiFactorAuthMethod::from(config('multi-factor.forceMethod'));
    }

    public function getAllowedMethods(): array
    {
        return Arr::map($this->getAllowedMethodNames(), fn($value): MultiFactorAuthMethod => MultiFactorAuthMethod::from($value));
    }

    public function getAllowedMethodNames(): array
    {
        return Arr::map(config('multi-factor.allowedMethods'), fn($method): string => Str::lower($method));
    }

    public function getMethodsByNames(array $names): array
    {
        return array_map(fn($name) => MultiFactorAuthMethod::from($name), $names);
    }

    public function isEmailOnlyLoginActive(): bool
    {
        return config('multi-factor.features.email-login.enabled');
    }

    public function isInSetupAfterLogin()
    {
        return $this->getFromSession(self::SETUP_AFTER_LOGIN);
    }

    public function endSetupAfterLogin()
    {
        $this->removeFromSession(self::SETUP_AFTER_LOGIN);
    }

    public function isEmailSent(): bool
    {
        return session()->has(self::EMAIL_SENT);
    }

    public function isCodeExpired(): bool
    {
        $sessionData = $this->getFromSession(self::CODE);

        return $sessionData && now()->greaterThan(Carbon::createFromTimestamp($sessionData['expires_at']));
    }

    public function getCode(): ?int
    {
        return $this->getFromSession(self::CODE, 'code');
    }

    protected function getFromSession(string $key, string $subKey = null): mixed
    {
        $data = session($key);
        return $subKey ? ($data[$subKey] ?? null) : $data;
    }

    protected function putInSession(string $key, mixed $value = true): void
    {
        session()->put($key, $value);
    }

    public function removeFromSession(string $key): void
    {
        session()->remove($key);
    }
}