<?php

namespace CybexGmbh\LaravelMultiFactor\Contracts;

use CybexGmbh\LaravelMultiFactor\Enums\MultiFactorAuthMethod as MultiFactorAuthMethodEnum;
use Illuminate\Http\RedirectResponse;

interface MultiFactorAuthMethod
{
    /**
     * @return MultiFactorChallengeViewResponseContract
     */
    public function authenticate(): MultiFactorChallengeViewResponseContract;

    /**
     * @return RedirectResponse
     */
    public function send(): RedirectResponse;

    /**
     * @return RedirectResponse
     */
    public function setup(): RedirectResponse;
}
