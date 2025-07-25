<?php

namespace Cybex\LaravelMultiFactor\Http\Middleware;

use Closure;
use Cybex\LaravelMultiFactor\Contracts\MultiFactorLoginViewResponseContract;
use Cybex\LaravelMultiFactor\Enums\MultiFactorAuthMethod;
use Cybex\LaravelMultiFactor\Enums\MultiFactorAuthMode;
use Illuminate\Http\Request;
use MFA;

class EnforceEmailOnlyLogin
{
    public function handle(Request $request, Closure $next)
    {
        if (MFA::isEmailOnlyLoginActive() && MultiFactorAuthMode::isForceMode() && MFA::getForceMethod(
            ) === MultiFactorAuthMethod::EMAIL) {

            $response = app(MultiFactorLoginViewResponseContract::class);
            return response($response->toResponse($request));
        }

        return $next($request);
    }
}
