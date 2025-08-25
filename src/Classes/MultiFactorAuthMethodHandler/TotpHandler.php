<?php

namespace Cybex\LaravelMultiFactor\Classes\MultiFactorAuthMethodHandler;

use Cybex\LaravelMultiFactor\Contracts\MultiFactorAuthMethodContract;
use Cybex\LaravelMultiFactor\Contracts\MultiFactorChallengeViewResponseContract;
use Cybex\LaravelMultiFactor\Contracts\MultiFactorSetupViewResponseContract;
use Cybex\LaravelMultiFactor\Enums\MultiFactorAuthMethod;
use Cybex\LaravelMultiFactor\Models\MultiFactorAuthMethod as MultiFactorAuthMethodModel;
use Illuminate\Foundation\Auth\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Laravel\Fortify\Actions\DisableTwoFactorAuthentication;
use MFA;

class TotpHandler implements MultiFactorAuthMethodContract
{
    protected MultiFactorAuthMethod $method = MultiFactorAuthMethod::TOTP;
    protected User $user;

    public function __construct()
    {
        $this->user = MFA::getUser();
    }

    public function challenge(): MultiFactorChallengeViewResponseContract
    {
        return app(MultiFactorChallengeViewResponseContract::class, [$this->user, $this->method]);
    }

    public function showSetup(): MultiFactorSetupViewResponseContract|RedirectResponse
    {
        return app(MultiFactorSetupViewResponseContract::class, [$this->user, $this->method]);
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

    public function delete(): void
    {
        $this->user->detachMultiFactorAuthMethod($this->method);

        app(DisableTwoFactorAuthentication::class)($this->user);
    }
}
