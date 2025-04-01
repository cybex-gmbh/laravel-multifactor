<?php

namespace CybexGmbh\LaravelTwoFactor\Http\Responses;

use CybexGmbh\LaravelTwoFactor\Contracts\MultiFactorLoginViewResponseContract;

class MultiFactorLoginViewResponse implements MultiFactorLoginViewResponseContract
{
    public function toResponse($request)
    {
        return view('laravel-two-factor::email-login');
    }
}