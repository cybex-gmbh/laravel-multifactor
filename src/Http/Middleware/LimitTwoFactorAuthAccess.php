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
        $userMethods = Auth::user()->getTwoFactorAuthMethods();
        $method = $request->route('method');
        $isVerified = TwoFactorAuthSession::VERIFIED->get();
        $isSetupInProgress = TwoFactorAuthSession::SETUP_IN_PROCESS->get();
        $configuredMode = TwoFactorAuthMode::fromConfig();
        $optionalMode = TwoFactorAuthMode::OPTIONAL;

        if (!$method->isAllowed()) {
            if ($isSetupInProgress && !$isVerified && $configuredMode === $optionalMode) {
                return redirect()->route('2fa.show');
            }

            if ($isVerified) {
                return redirect()->route('2fa.setup');
            }
        }

        if (!$method->isUserMethod() && !$isVerified) {
            if ($userMethods) {
                return redirect()->route('2fa.show');
            }

            if ($configuredMode === $optionalMode && !$isSetupInProgress) {
                return redirect()->route('projects.index');
            }
        }

        return $next($request);
    }
}
