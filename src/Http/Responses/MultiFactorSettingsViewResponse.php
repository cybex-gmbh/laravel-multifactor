<?php

namespace CybexGmbh\LaravelTwoFactor\Http\Responses;

use CybexGmbh\LaravelTwoFactor\Contracts\MultiFactorSettingsViewResponseContract;
use CybexGmbh\LaravelTwoFactor\Enums\TwoFactorAuthMethod;
use Illuminate\Foundation\Auth\User;

class MultiFactorSettingsViewResponse implements MultiFactorSettingsViewResponseContract
{
    protected User $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }
    public function toResponse($request)
    {
        $user = $this->user;

        $methods = $user->getTwoFactorAuthMethods();
        $methods = $methods ?: TwoFactorAuthMethod::getAllowedMethods();

        return view('laravel-two-factor::settings', compact('user', 'methods'));
    }
}