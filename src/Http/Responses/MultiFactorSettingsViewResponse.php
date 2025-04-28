<?php

namespace CybexGmbh\LaravelMultiFactor\Http\Responses;

use CybexGmbh\LaravelMultiFactor\Contracts\MultiFactorSettingsViewResponseContract;
use CybexGmbh\LaravelMultiFactor\Enums\MultiFactorAuthMethod;
use CybexGmbh\LaravelMultiFactor\Enums\MultiFactorAuthMode;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Auth\User;

class MultiFactorSettingsViewResponse implements MultiFactorSettingsViewResponseContract
{
    protected User $user;

    /**
     * @param User $user
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * @param $request
     * @return Application|Factory|object|View
     */
    public function toResponse($request)
    {
        $user = $this->user;
        $userMethods = $user->getMultiFactorAuthMethods();
        $userMethodsAmount = count($userMethods);
        $allowedMethods = MultiFactorAuthMethod::getAllowedMethods();
        $mfaMode = MultiFactorAuthMode::fromConfig();
        $methods = $user->getUserMethodsWithRemainingAllowedMethods($allowedMethods, $userMethods);

        return view('laravel-multi-factor::settings', compact(['user', 'userMethodsAmount', 'methods', 'mfaMode']));
    }
}
