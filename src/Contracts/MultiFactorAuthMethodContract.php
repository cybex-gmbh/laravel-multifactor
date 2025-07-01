<?php

namespace Cybex\LaravelMultiFactor\Contracts;

use Illuminate\Http\RedirectResponse;

interface MultiFactorAuthMethodContract
{
    /**
     * Sets up the multi-factor authentication method and redirects the user to the intended or settings page.
     *
     * @return RedirectResponse
     */
    public function setup(): RedirectResponse;

    public function challenge(): MultiFactorChallengeViewResponseContract;
}
