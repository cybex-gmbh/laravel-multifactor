<?php

namespace CybexGmbh\LaravelMultiFactor\Http\Responses;

use CybexGmbh\LaravelMultiFactor\Contracts\MultiFactorLoginViewResponseContract;

class MultiFactorLoginViewResponse implements MultiFactorLoginViewResponseContract
{
    public function toResponse($request)
    {
        return view('laravel-multi-factor::email-login');
    }
}
