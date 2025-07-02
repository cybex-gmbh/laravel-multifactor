<?php

namespace Cybex\LaravelMultiFactor\Http\Responses;

use Cybex\LaravelMultiFactor\Contracts\MultiFactorSettingsViewResponseContract;
use Cybex\LaravelMultiFactor\Enums\MultiFactorAuthMethod;
use Cybex\LaravelMultiFactor\Enums\MultiFactorAuthMode;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Auth\User;

class MultiFactorSettingsViewResponse implements MultiFactorSettingsViewResponseContract
{
    public function __construct(protected User $user)
    {}

    public function toResponse($request)
    {
        $user = $this->user;
        $userMethods = $user->getMultiFactorAuthMethods();
        $userMethodsAmount = count($userMethods);
        $allowedMethods = MultiFactorAuthMethod::getAllowedMethods();
        $mfaMode = MultiFactorAuthMode::fromConfig();
        $isOptionalMode = MultiFactorAuthMode::isOptionalMode();
        $methods = $user->getUserMethodsWithRemainingAllowedMethods($allowedMethods, $userMethods);

        return view('laravel-multi-factor::pages.settings', compact(['user', 'userMethodsAmount', 'methods', 'mfaMode', 'isOptionalMode']));
    }
}
