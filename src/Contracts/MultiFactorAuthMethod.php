<?php

namespace CybexGmbh\LaravelMultiFactor\Contracts;

use CybexGmbh\LaravelMultiFactor\Enums\MultiFactorAuthMethod as MultiFactorAuthMethodEnum;
use Illuminate\Http\RedirectResponse;

interface MultiFactorAuthMethod
{
    /**
     * Sets up the multi-factor authentication method and redirects the user to the intended or settings page.
     *
     * @return RedirectResponse
     */
    public function setup(): RedirectResponse;

    /**
     * @return MultiFactorChallengeViewResponseContract
     */
    public function challenge(): MultiFactorChallengeViewResponseContract;
}
