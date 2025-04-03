<?php

namespace CybexGmbh\LaravelTwoFactor\Http\Responses;

use CybexGmbh\LaravelTwoFactor\Contracts\MultiFactorChooseViewResponseContract;

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

        return view('laravel-two-factor::choose-method', compact('userMethods'));
    }
}