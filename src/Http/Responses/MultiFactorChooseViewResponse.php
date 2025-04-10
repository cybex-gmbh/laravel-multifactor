<?php

namespace CybexGmbh\LaravelTwoFactor\Http\Responses;

use CybexGmbh\LaravelTwoFactor\Contracts\MultiFactorChooseViewResponseContract;
use CybexGmbh\LaravelTwoFactor\Enums\TwoFactorAuthSession;

class MultiFactorChooseViewResponse implements MultiFactorChooseViewResponseContract
{
    protected array $userMethods;

    public function __construct(array $userMethods)
    {
        $this->userMethods = $userMethods;
    }
    public function toResponse($request)
    {
        $userMethods = $this->userMethods;
        $isVerified = TwoFactorAuthSession::VERIFIED->get();

        return view('laravel-two-factor::choose-method', compact(['userMethods', 'isVerified']));
    }
}