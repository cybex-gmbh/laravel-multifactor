<?php

namespace CybexGmbh\LaravelTwoFactor\Contracts;

use Illuminate\Http\RedirectResponse;

interface TwoFactorAuthMethod
{
    public function authenticate(): MultiFactorChallengeViewResponseContract;
    public function send(): RedirectResponse;
    public function setup(): RedirectResponse;
}