<?php

namespace CybexGmbh\LaravelTwoFactor\Http\Responses;

use CybexGmbh\LaravelTwoFactor\Contracts\TwoFactorChallengeViewResponseContract;

class TwoFactorChallengeViewResponse implements TwoFactorChallengeViewResponseContract
{
    public function toResponse($request)
    {
        return view('laravel-two-factor::challenge');
    }
}