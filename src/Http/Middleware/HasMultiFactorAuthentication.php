<?php

namespace CybexGmbh\LaravelMultiFactor\Http\Middleware;

use Closure;
use CybexGmbh\LaravelMultiFactor\Enums\MultiFactorAuthMode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use MFA;

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

        if ($user->multiFactorAuthMethods()->exists() && !MFA::getVerified()) {

            if (MultiFactorAuthMode::fromConfig() === MultiFactorAuthMode::OPTIONAL && !$user->getAllowedMultiFactorAuthMethods() && $user->getUnallowedMultiFactorAuthMethods()) {
                MFA::setVerified();
                return $next($request);
            }

            return redirect()->route('mfa.show');
        }

        MFA::setVerified();
        return $next($request);
    }
}
