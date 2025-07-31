<?php

namespace Cybex\LaravelMultiFactor\Http\Responses;

use Cybex\LaravelMultiFactor\Contracts\MultiFactorSettingsViewResponseContract;
use Cybex\LaravelMultiFactor\Enums\MultiFactorAuthMode;
use Illuminate\Foundation\Auth\User;
use MFA;

class MultiFactorSettingsViewResponse implements MultiFactorSettingsViewResponseContract
{
    public function __construct(protected User $user)
    {
    }

    public function toResponse($request)
    {
        $user = $this->user;
        $userMethodsAmount = count($user->getMultiFactorAuthMethods());
        $mfaMode = MultiFactorAuthMode::fromConfig();
        $isOptionalMode = MultiFactorAuthMode::isOptionalMode();
        $methods = $user->getUserMethodsWithRemainingAllowedMethods();

        return view('laravel-multi-factor::pages.settings', compact(['user', 'userMethodsAmount', 'methods', 'mfaMode', 'isOptionalMode']));
    }
}
