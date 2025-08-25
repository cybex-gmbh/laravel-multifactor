<?php

namespace Cybex\LaravelMultiFactor\Http\Responses;

use Cybex\LaravelMultiFactor\Contracts\MultiFactorSetupViewResponseContract;
use Cybex\LaravelMultiFactor\Enums\MultiFactorAuthMethod;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Auth\User;

class MultiFactorSetupViewResponse implements MultiFactorSetupViewResponseContract
{
    protected User $user;
    protected MultiFactorAuthMethod $mfaMethod;

    /**
     * @param User $user
     * @param MultiFactorAuthMethod $mfaMethod
     */
    public function __construct(User $user, MultiFactorAuthMethod $mfaMethod)
    {
        $this->user = $user;
        $this->mfaMethod = $mfaMethod;
    }

    /**
     * @param $request
     * @return Factory|Application|object|View
     */
    public function toResponse($request)
    {
        $user = $this->user;
        $mfaMethod = $this->mfaMethod;
        $hasStartedTotpSetup = $user->hasStartedTotpSetup();

        return match ($mfaMethod) {
            MultiFactorAuthMethod::TOTP => view('laravel-multi-factor::pages.totp-setup', compact(['user', 'mfaMethod', 'hasStartedTotpSetup'])),
        };
    }
}
