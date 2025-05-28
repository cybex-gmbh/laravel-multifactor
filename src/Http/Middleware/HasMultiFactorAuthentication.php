<?php

namespace CybexGmbh\LaravelMultiFactor\Http\Middleware;

use Closure;
use CybexGmbh\LaravelMultiFactor\Enums\MultiFactorAuthMode;
use CybexGmbh\LaravelMultiFactor\Enums\MultiFactorAuthSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class HasMultiFactorAuthentication
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if ($user->multiFactorAuthMethods()->exists() && !MultiFactorAuthSession::VERIFIED->get()) {

            if (MultiFactorAuthMode::fromConfig() === MultiFactorAuthMode::OPTIONAL && !$user->getFilteredMFAMethods() && $user->getFilteredMFAMethods(false)) {
                MultiFactorAuthSession::VERIFIED->put();
                return $next($request);
            }

            return redirect()->route('mfa.show');
        }

        MultiFactorAuthSession::VERIFIED->put();
        return $next($request);
    }
}
