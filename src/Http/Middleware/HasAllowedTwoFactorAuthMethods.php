<?php

namespace CybexGmbh\LaravelTwoFactor\Http\Middleware;

use Closure;
use CybexGmbh\LaravelTwoFactor\Enums\TwoFactorAuthMode;
use CybexGmbh\LaravelTwoFactor\Enums\TwoFactorAuthSession;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class HasAllowedTwoFactorAuthMethods
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        $allowed2faMethods = $user->getAllowed2FAMethods();

        if ($allowed2faMethods || TwoFactorAuthMode::fromConfig() === TwoFactorAuthMode::OPTIONAL) {
            return $next($request);
        }

        TwoFactorAuthSession::SETUP_IN_PROCESS->put();
        return redirect()->route('2fa.setup');
    }
}
