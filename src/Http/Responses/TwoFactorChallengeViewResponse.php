<?php

namespace CybexGmbh\LaravelTwoFactor\Http\Responses;

use CybexGmbh\LaravelTwoFactor\Contracts\TwoFactorChallengeViewResponseContract;
use CybexGmbh\LaravelTwoFactor\Enums\TwoFactorAuthMethod;
use Illuminate\Foundation\Auth\User;

class TwoFactorChallengeViewResponse implements TwoFactorChallengeViewResponseContract
{
    protected User $user;
    protected TwoFactorAuthMethod $method;

    public function __construct(User $user, TwoFactorAuthMethod $method)
    {
        $this->user = $user;
        $this->method = $method;
    }
    public function toResponse($request)
    {
        $user = $this->user;
        $method = $this->method;

        return view('laravel-two-factor::email-challenge', compact('user', 'method'));
    }
}