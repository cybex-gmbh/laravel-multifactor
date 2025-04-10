<?php

namespace CybexGmbh\LaravelTwoFactor\Http\Middleware;

use Closure;
use CybexGmbh\LaravelTwoFactor\Contracts\MultiFactorLoginViewResponseContract;
use CybexGmbh\LaravelTwoFactor\Enums\TwoFactorAuthMethod;
use CybexGmbh\LaravelTwoFactor\Enums\TwoFactorAuthMode;
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
        if (config('two-factor.routes.email-login.enabled') && TwoFactorAuthMode::fromConfig() === TwoFactorAuthMode::FORCE && TwoFactorAuthMethod::getForceMethod(
            ) === TwoFactorAuthMethod::EMAIL) {

            $response = app(MultiFactorLoginViewResponseContract::class);
            return response($response->toResponse($request));
        }

        return $next($request);
    }
}
