<?php

namespace Cybex\LaravelMultiFactor\Classes\MultiFactorAuthMethodHandler;

use Cybex\LaravelMultiFactor\Contracts\MultiFactorAuthMethodContract;
use Cybex\LaravelMultiFactor\Contracts\MultiFactorChallengeViewResponseContract;
use Cybex\LaravelMultiFactor\Enums\MultiFactorAuthMethod;
use Cybex\LaravelMultiFactor\Notifications\MultiFactorCodeNotification;
use Illuminate\Foundation\Auth\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\URL;
use Cybex\LaravelMultiFactor\Models\MultiFactorAuthMethod as MultiFactorAuthMethodModel;
use MFA;

class EmailHandler implements MultiFactorAuthMethodContract
{
    protected MultiFactorAuthMethod $method = MultiFactorAuthMethod::EMAIL;
    protected User $user;

    public function __construct()
    {
        $this->user = MFA::getUser();
    }

    public function setup(): RedirectResponse
    {
        $this->user->multiFactorAuthMethods()->attach(MultiFactorAuthMethodModel::firstOrCreate([
            'type' => $this->method,
        ]));

        if (!Auth::check()) {
            Auth::login($this->user);
        }

        if (MFA::isInSetupAfterLogin()) {
            MFA::endSetupAfterLogin();

            return redirect()->intended();
        }

        return redirect()->route('mfa.settings', $this->user);
    }

    public function challenge(): MultifactorChallengeViewResponseContract
    {
        if (!MFA::isEmailSent()) {
            $this->sendEmail();
            MFA::setEmailSent();
        }

        return app(MultiFactorChallengeViewResponseContract::class, [$this->user, $this->method]);
    }

    public function sendEmail(): RedirectResponse
    {
        $code = random_int(100000, 999999);
        $userKey = $this->user->getKey();
        $expiresAt = now()->addMinutes(10);

        MFA::setAuthCode($code, $expiresAt->timestamp);

        if (MFA::isEmailOnlyLoginActive()) {
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
}
