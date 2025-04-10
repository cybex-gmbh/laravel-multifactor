<?php

namespace CybexGmbh\LaravelTwoFactor\Http\Middleware;

use Closure;
use CybexGmbh\LaravelTwoFactor\Enums\TwoFactorAuthMode;
use CybexGmbh\LaravelTwoFactor\Enums\TwoFactorAuthSession;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfTwoFactorAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (TwoFactorAuthSession::VERIFIED->get() && TwoFactorAuthMode::fromConfig() === TwoFactorAuthMode::FORCE && $request->route('method')?->isUserMethod()) {
            return redirect()->intended();
       }

        return $next($request);
    }
}
