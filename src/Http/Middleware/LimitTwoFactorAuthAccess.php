<?php

namespace CybexGmbh\LaravelTwoFactor\Http\Middleware;

use Closure;
use CybexGmbh\LaravelTwoFactor\Enums\TwoFactorAuthMode;
use CybexGmbh\LaravelTwoFactor\Enums\TwoFactorAuthSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class LimitTwoFactorAuthAccess
{
    /**
     * Handle an incoming request.
     *
     * @param Closure(Request): (Response) $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $method = $request->route('method');
        $isVerified = TwoFactorAuthSession::VERIFIED->get();

        if (($method->isAllowed() && !$method->isUserMethod()) && !$isVerified) {
            return redirect()->route('2fa.show');
        }

        if (!$method->isAllowed() && !$method->isUserMethod() && !$isVerified) {
            return redirect()->route('2fa.show');
        }

        if ((!$method->isAllowed() || $method->isUserMethod()) && $isVerified) {
            if (TwoFactorAuthMode::fromConfig() === TwoFactorAuthMode::FORCE) {
                return redirect()->back();
            }

            return redirect()->route('2fa.settings', Auth::user());
        }

        return $next($request);
    }
}
