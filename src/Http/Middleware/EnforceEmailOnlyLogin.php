<?php

namespace CybexGmbh\LaravelMultiFactor\Http\Middleware;

use Closure;
use CybexGmbh\LaravelMultiFactor\Contracts\MultiFactorLoginViewResponseContract;
use CybexGmbh\LaravelMultiFactor\Enums\MultiFactorAuthMethod;
use CybexGmbh\LaravelMultiFactor\Enums\MultiFactorAuthMode;
use Illuminate\Http\Request;

class EnforceEmailOnlyLogin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        if (MultiFactorAuthMethod::isEmailOnlyLoginActive() && MultiFactorAuthMode::isForceMode() && MultiFactorAuthMethod::getForceMethod(
            ) === MultiFactorAuthMethod::EMAIL) {

            $response = app(MultiFactorLoginViewResponseContract::class);
            return response($response->toResponse($request));
        }

        return $next($request);
    }
}
