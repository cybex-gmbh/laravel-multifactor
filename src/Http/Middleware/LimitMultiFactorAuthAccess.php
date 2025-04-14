<?php

namespace CybexGmbh\LaravelMultiFactor\Http\Middleware;

use Closure;
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

        if (!$isVerified && !$method->isUserMethod()) {
            return redirect()->route('mfa.show');
        }

        if ($isVerified && (!$method->isAllowed() || $method->isUserMethod())) {
            if (MultiFactorAuthMode::fromConfig() === MultiFactorAuthMode::FORCE) {
                return redirect()->back();
            }

            return redirect()->route('mfa.settings', Auth::user());
        }

        return $next($request);
    }
}
