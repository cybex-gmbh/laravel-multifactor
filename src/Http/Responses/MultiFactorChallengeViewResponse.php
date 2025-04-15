<?php

namespace CybexGmbh\LaravelMultiFactor\Http\Responses;

use CybexGmbh\LaravelMultiFactor\Contracts\MultiFactorChallengeViewResponseContract;
use CybexGmbh\LaravelMultiFactor\Enums\MultiFactorAuthMethod;
use Illuminate\Foundation\Auth\User;

class MultiFactorChallengeViewResponse implements MultiFactorChallengeViewResponseContract
{
    protected User $user;
    protected MultiFactorAuthMethod $method;

    public function __construct(User $user, MultiFactorAuthMethod $method)
    {
        $this->user = $user;
        $this->method = $method;
    }
    public function toResponse($request)
    {
        $user = $this->user;
        $method = $this->method;

        return view('laravel-multi-factor::email-challenge', compact('user', 'method'));
    }
}
