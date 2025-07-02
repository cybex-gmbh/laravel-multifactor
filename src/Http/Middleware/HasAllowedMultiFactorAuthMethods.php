<?php

namespace Cybex\LaravelMultiFactor\Http\Middleware;

use Closure;
use Cybex\LaravelMultiFactor\Enums\MultiFactorAuthMode;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use MFA;

class HasAllowedMultiFactorAuthMethods
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        $allowed2faMethods = $user->getAllowedMultiFactorAuthMethods();

        if ($allowed2faMethods || MultiFactorAuthMode::isOptionalMode()) {
            return $next($request);
        }

        MFA::setVerified();
        return redirect()->route('mfa.setup');
    }
}
