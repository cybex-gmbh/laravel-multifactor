<?php

namespace CybexGmbh\LaravelTwoFactor\Classes\TwoFactorAuthMethodHandler;

use CybexGmbh\LaravelTwoFactor\Contracts\MultiFactorChallengeViewResponseContract;
use CybexGmbh\LaravelTwoFactor\Contracts\TwoFactorAuthMethod as TwoFactorAuthMethodInterface;
use CybexGmbh\LaravelTwoFactor\Enums\TwoFactorAuthMethod;
use CybexGmbh\LaravelTwoFactor\Enums\TwoFactorAuthSession;
use CybexGmbh\LaravelTwoFactor\Notifications\TwoFactorCodeNotification;
use Illuminate\Foundation\Auth\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class EmailHandler implements TwoFactorAuthMethodInterface
{
    protected TwoFactorAuthMethod $method = TwoFactorAuthMethod::EMAIL;
    protected User $user;

    public function __construct()
    {
        $this->user = Auth::user();
    }

    public function authenticate(): MultifactorChallengeViewResponseContract
    {
        $sessionKey = TwoFactorAuthSession::EMAIL_SENT;

        if (!session()->has($sessionKey->value)) {
            $this->send();
            $sessionKey->put();
        }

        return app(MultiFactorChallengeViewResponseContract::class, [$this->user, $this->method]);
    }

    public function send(): RedirectResponse
    {
        $code = random_int(100000, 999999);
        $userKey = $this->user->getKey();

        TwoFactorAuthSession::CODE->put($code);

        $this->user->notify(new TwoFactorCodeNotification(TwoFactorAuthMethod::EMAIL, $code, $userKey));

        return redirect()->back();
    }

    public function setup(): RedirectResponse
    {
        $this->user->twoFactorAuthMethods()->firstOrCreate([
            'type' => TwoFactorAuthMethod::EMAIL,
        ]);

        if (TwoFactorAuthSession::VERIFIED->get()) {
            return redirect()->route('2fa.settings', $this->user);
        }

        return redirect()->intended();
    }
}
