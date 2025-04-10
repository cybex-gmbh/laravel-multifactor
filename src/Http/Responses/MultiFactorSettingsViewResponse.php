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

        $userMethods = $user->getTwoFactorAuthMethods();
        $allowedMethods = TwoFactorAuthMethod::getAllowedMethods();

        // refactor to method in User Model
        $methods = array_filter($allowedMethods, function ($method) use ($userMethods) {
            return in_array($method, $userMethods) || !in_array($method, $userMethods);
        });

        return view('laravel-two-factor::settings', compact('user', 'methods'));
    }
}