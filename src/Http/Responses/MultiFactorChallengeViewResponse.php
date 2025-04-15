<?php

namespace CybexGmbh\LaravelMultiFactor\Http\Responses;

use CybexGmbh\LaravelMultiFactor\Contracts\MultiFactorChallengeViewResponseContract;
use CybexGmbh\LaravelMultiFactor\Enums\MultiFactorAuthMethod;
use Illuminate\Foundation\Auth\User;

class MultiFactorChallengeViewResponse implements MultiFactorChallengeViewResponseContract
{
    protected User $user;
    protected MultiFactorAuthMethod $mfaMethod;

    public function __construct(User $user, MultiFactorAuthMethod $mfaMethod)
    {
        $this->user = $user;
        $this->mfaMethod = $mfaMethod;
    }
    public function toResponse($request)
    {
        $user = $this->user;
        $mfaMethod = $this->mfaMethod;
        $authenticationMethod = MultiFactorAuthMethod::isEmailOnlyLoginActive() ? 'link' : 'code';

        return view('laravel-multi-factor::email-challenge', compact(['user', 'mfaMethod', 'authenticationMethod']));
    }
}
