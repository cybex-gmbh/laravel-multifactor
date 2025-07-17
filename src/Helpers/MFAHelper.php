<?php

namespace Cybex\LaravelMultiFactor\Helpers;

use App\Models\User;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class MFAHelper
{
    public const CODE = 'two_factor_auth_code';
    public const EMAIL_SENT = 'two_factor_auth_email_sent';
    public const VERIFIED = 'two_factor_auth_verified';
    public const SETUP_AFTER_LOGIN = 'two_factor_auth_setup_after_login';
    public const LOGIN_ID = 'login.id';
    public const LOGIN_REMEMBER = 'login.remember';

    public function clear(): void
    {
        session()->forget([
            self::CODE,
            self::EMAIL_SENT,
            self::VERIFIED,
            self::SETUP_AFTER_LOGIN
        ]);
    }

    public function setLoginIdAndRemember(User $user, bool $remember = false): void
    {
        $this->put(self::LOGIN_ID, $user->getKey());
        $this->put(self::LOGIN_REMEMBER, $remember);
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

    public function isPersistentLogin(): bool
    {
        return $this->isInSetupAfterLogin() && !collect(session()->all())->keys()->contains(fn($key) => str_starts_with($key, 'login_web_'));
    }

    public function getUser(): Authenticatable|User
    {
        return auth()->user() ?? User::find($this->get(self::LOGIN_ID));
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

    public function login(bool $remember = false): void
    {
        $this->clear();
        $this->setVerified();
        Auth::guard()->login($this->getUser(), $remember);
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