<?php

namespace CybexGmbh\LaravelMultiFactor\Http\Middleware;

use Closure;
use CybexGmbh\LaravelMultiFactor\Enums\MultiFactorAuthMode;
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

        $allowed2faMethods = $user->getAllowed2FAMethods();

        if ($allowed2faMethods || MultiFactorAuthMode::fromConfig() === MultiFactorAuthMode::OPTIONAL) {
            return $next($request);
        }

        return redirect()->route('mfa.setup');
    }
}
