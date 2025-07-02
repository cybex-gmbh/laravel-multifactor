<?php

namespace Cybex\LaravelMultiFactor\Http\Middleware;

use Closure;
use Cybex\LaravelMultiFactor\Enums\MultiFactorAuthMode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use MFA;

class HasMultiFactorAuthentication
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if ($user->multiFactorAuthMethods()->exists() && !MFA::isVerified()) {

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
