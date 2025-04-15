<?php

namespace CybexGmbh\LaravelMultiFactor\Contracts;

use Illuminate\Http\RedirectResponse;

interface MultiFactorAuthMethod
{
    public function authenticate(): MultiFactorChallengeViewResponseContract;
    public function send(): RedirectResponse;
    public function setup(): RedirectResponse;
}
