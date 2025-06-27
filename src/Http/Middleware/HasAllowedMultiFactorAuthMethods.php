<?php

namespace CybexGmbh\LaravelMultiFactor\Http\Middleware;

use Closure;
use CybexGmbh\LaravelMultiFactor\Enums\MultiFactorAuthMode;
use CybexGmbh\LaravelMultiFactor\Enums\MultiFactorAuthSession;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class HasAllowedMultiFactorAuthMethods
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        $allowed2faMethods = $user->getFilteredMFAMethods();

        if ($allowed2faMethods || MultiFactorAuthMode::fromConfig() === MultiFactorAuthMode::OPTIONAL) {
            return $next($request);
        }

        MultiFactorAuthSession::SETUP_AFTER_LOGIN->put();
        return redirect()->route('mfa.setup');
    }
}
