<?php

namespace CybexGmbh\LaravelMultiFactor\Http\Middleware;

use Closure;
use CybexGmbh\LaravelMultiFactor\Enums\MultiFactorAuthMethod;
use CybexGmbh\LaravelMultiFactor\Enums\MultiFactorAuthMode;
use CybexGmbh\LaravelMultiFactor\Enums\MultiFactorAuthSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class LimitMultiFactorAuthAccess
{
    /**
     * Handle an incoming request.
     *
     * @param Closure(Request): (Response) $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $method = $request->route('method');
        $isVerified = MultiFactorAuthSession::VERIFIED->get();
        $isForceMode = MultiFactorAuthMode::isForceMode();

        if (!$isVerified) {
            if (!$method->isUserMethod()) {
                return redirect()->route('mfa.show');
            }

            if (!$method->isAllowed() && Auth::user()->hasAllowedMultiFactorAuthMethods()) {
                return redirect()->route('mfa.show');
            }

            if ($isForceMode && !$method->isForceMethod()) {
                return redirect()->route('mfa.method', ['method' => MultiFactorAuthmethod::getForceMethod()]);
            }
        }

        if ($isVerified && (!$method->isAllowed() || $method->isUserMethod())) {
            if ($isForceMode) {
                return redirect()->back();
            }

            return redirect()->route('mfa.settings', Auth::user());
        }

        return $next($request);
    }
}
