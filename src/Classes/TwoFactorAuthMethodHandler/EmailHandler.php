<?php

namespace CybexGmbh\LaravelMultiFactor\Classes\TwoFactorAuthMethodHandler;

use CybexGmbh\LaravelMultiFactor\Contracts\MultiFactorAuthMethod as MultiFactorAuthMethodInterface;
use CybexGmbh\LaravelMultiFactor\Contracts\MultiFactorChallengeViewResponseContract;
use CybexGmbh\LaravelMultiFactor\Enums\MultiFactorAuthMethod;
use CybexGmbh\LaravelMultiFactor\Enums\MultiFactorAuthSession;
use CybexGmbh\LaravelMultiFactor\Notifications\MultiFactorCodeNotification;
use Illuminate\Foundation\Auth\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\URL;

class EmailHandler implements MultiFactorAuthMethodInterface
{
    protected MultiFactorAuthMethod $method = MultiFactorAuthMethod::EMAIL;
    protected User $user;

    public function __construct()
    {
        $this->user = Auth::user();
    }

    /**
     * @return MultiFactorChallengeViewResponseContract
     */
    public function authenticate(): MultifactorChallengeViewResponseContract
    {
        $sessionKey = MultiFactorAuthSession::EMAIL_SENT;

        if (!session()->has($sessionKey->value)) {
            $this->send();
            $sessionKey->put();
        }

        return app(MultiFactorChallengeViewResponseContract::class, [$this->user, $method ?? $this->method]);
    }

    /**
     * @return RedirectResponse
     */
    public function send(): RedirectResponse
    {
        $code = random_int(100000, 999999);
        $userKey = $this->user->getKey();
        $expiresAt = now()->addMinutes(10);

        MultiFactorAuthSession::CODE->put(['code' => $code, 'expires_at' => $expiresAt]);

        if (MultiFactorAuthMethod::isEmailOnlyLoginActive()) {
            $url = URL::temporarySignedRoute(
                'mfa.login',
                $expiresAt,
                [
                    'method' => $this->method,
                    'user' => $userKey,
                    'code' => $code
                ]
            );
        }

        $this->user->notify(new MultiFactorCodeNotification($url ?? null));

        return redirect()->back();
    }

    /**
     * @return RedirectResponse
     */
    public function setup(): RedirectResponse
    {
        $this->user->multiFactorAuthMethods()->firstOrCreate([
            'type' => MultiFactorAuthMethod::EMAIL,
        ]);

        if (MultiFactorAuthSession::SETUP_AFTER_LOGIN->get()) {
            MultiFactorAuthSession::SETUP_AFTER_LOGIN->remove();

            return redirect()->intended();
        }

        return redirect()->route('mfa.settings', $this->user);
    }
}
