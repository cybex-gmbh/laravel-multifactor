<?php

namespace CybexGmbh\LaravelTwoFactor\Http\Middleware;

use Closure;
use CybexGmbh\LaravelTwoFactor\Enums\TwoFactorAuthMode;
use CybexGmbh\LaravelTwoFactor\Enums\TwoFactorAuthSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class HasTwoFactorAuthentication
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if ($user->twoFactorAuthMethods()->exists() && !TwoFactorAuthSession::VERIFIED->get()) {

            if (TwoFactorAuthMode::fromConfig() === TwoFactorAuthMode::OPTIONAL && !$user->getAllowed2FAMethods() && $user->getUnallowedMethodsNames()) {
                TwoFactorAuthSession::VERIFIED->put();
                return $next($request);
            }

            return redirect()->route('2fa.show');
        }

        TwoFactorAuthSession::VERIFIED->put();
        return $next($request);
    }
}
