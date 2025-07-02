<?php

namespace Cybex\LaravelMultiFactor\Http\Middleware;

use Closure;
use Cybex\LaravelMultiFactor\Enums\MultiFactorAuthMethod;
use Cybex\LaravelMultiFactor\Enums\MultiFactorAuthMode;
use MFA;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class LimitMultiFactorAuthAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $method = $request->route('method');
        $isVerified = MFA::isVerified();
        $isForceMode = MultiFactorAuthMode::isForceMode();

        if (!$isVerified) {
            if (!$method->isUserMethod()) {
                return redirect()->route('mfa.show');
            }

            if (!$method->isAllowed() && Auth::user()->hasAllowedMultiFactorAuthMethods()) {
                return redirect()->route('mfa.show');
            }

            if ($isForceMode) {
                $forceMethod = MultiFactorAuthMethod::getForceMethod();

                if (!$method->isForceMethod() && $forceMethod->isAllowed() && $forceMethod->isUserMethod()) {
                    return redirect()->route('mfa.method', ['method' => $forceMethod]);
                }
            }
        } else if (!$method->isAllowed() || $method->isUserMethod()) {
            if ($isForceMode) {
                return redirect()->back();
            }

            return redirect()->route('mfa.settings', Auth::user());
        } else if ($isForceMode && !$method->isForceMethod() && MFA::isInSetupAfterLogin()) {
            return redirect()->route('mfa.setup');
        }

        return $next($request);
    }
}
