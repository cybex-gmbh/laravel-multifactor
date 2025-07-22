<?php

namespace Cybex\LaravelMultiFactor\Http\Responses;

use Cybex\LaravelMultiFactor\Contracts\MultiFactorChallengeViewResponseContract;
use Cybex\LaravelMultiFactor\Enums\MultiFactorAuthMethod;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Auth\User;
use MFA;

class MultiFactorChallengeViewResponse implements MultiFactorChallengeViewResponseContract
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
        $authenticationMethod = MultiFactorAuthMethod::isEmailOnlyLoginActive() ? 'link' : 'code';

        if ($mfaMethod === MultiFactorAuthMethod::TOTP) {
            if(empty(MFA::getUser()->two_factor_confirmed_at) && isset(MFA::getUser()->two_factor_secret)) {
                $action = 'two-factor.confirm';
            } else {
                $action = 'mfa.store';
            }
        }

        return match ($mfaMethod) {
            MultiFactorAuthMethod::EMAIL => view('laravel-multi-factor::pages.email-challenge', compact(['user', 'mfaMethod', 'authenticationMethod'])),
            MultiFactorAuthMethod::TOTP => view('laravel-multi-factor::pages.totp-challenge', compact(['user', 'mfaMethod', 'authenticationMethod', 'action'])),
        };
    }
}
