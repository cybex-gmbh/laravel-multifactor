<?php

namespace CybexGmbh\LaravelMultiFactor\Classes\TwoFactorAuthMethodHandler;

use CybexGmbh\LaravelMultiFactor\Contracts\MultiFactorAuthMethod as TwoFactorAuthMethodInterface;
use CybexGmbh\LaravelMultiFactor\Contracts\MultiFactorChallengeViewResponseContract;
use CybexGmbh\LaravelMultiFactor\Enums\MultiFactorAuthMethod;
use CybexGmbh\LaravelMultiFactor\Enums\MultiFactorAuthSession;
use CybexGmbh\LaravelMultiFactor\Notifications\MultiFactorCodeNotification;
use Illuminate\Foundation\Auth\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class EmailHandler implements TwoFactorAuthMethodInterface
{
    protected MultiFactorAuthMethod $method = MultiFactorAuthMethod::EMAIL;
    protected User $user;

    public function __construct()
    {
        $this->user = Auth::user();
    }

    public function authenticate(): MultifactorChallengeViewResponseContract
    {
        $sessionKey = MultiFactorAuthSession::EMAIL_SENT;

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

        MultiFactorAuthSession::CODE->put($code);

        $this->user->notify(new MultiFactorCodeNotification(MultiFactorAuthMethod::EMAIL, $code, $userKey));

        return redirect()->back();
    }

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
