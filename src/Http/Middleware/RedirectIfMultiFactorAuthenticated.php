<?php

namespace Cybex\LaravelMultiFactor\Http\Middleware;

use Closure;
use Cybex\LaravelMultiFactor\Enums\MultiFactorAuthMode;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use MFA;

class RedirectIfMultiFactorAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (MFA::isVerified() && MultiFactorAuthMode::isForceMode() && $request->route('method')?->isUserMethod()) {
            return redirect()->intended();
        }

        return $next($request);
    }
}
