<?php

namespace CybexGmbh\LaravelMultiFactor\Http\Responses;

use CybexGmbh\LaravelMultiFactor\Contracts\MultiFactorSettingsViewResponseContract;
use CybexGmbh\LaravelMultiFactor\Enums\MultiFactorAuthMethod;
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

        $userMethods = $user->getMultiFactorAuthMethods();
        $allowedMethods = MultiFactorAuthMethod::getAllowedMethods();

        $methods = $user->getUserMethodsWithRemainingAllowedMethods($allowedMethods, $userMethods);

        return view('laravel-multi-factor::settings', compact('user', 'methods'));
    }
}
