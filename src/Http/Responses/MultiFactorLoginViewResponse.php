<?php

namespace Cybex\LaravelMultiFactor\Http\Responses;

use Cybex\LaravelMultiFactor\Contracts\MultiFactorLoginViewResponseContract;
use MFA;

class MultiFactorLoginViewResponse implements MultiFactorLoginViewResponseContract
{
    public function toResponse($request)
    {
        if (MFA::isEmailOnlyLoginActive()) {
            return view('laravel-multi-factor::pages.email-login');
        } else {
            return view('laravel-multi-factor::auth.login');
        }
    }
}
