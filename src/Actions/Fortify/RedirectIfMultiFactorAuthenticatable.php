<?php

namespace Cybex\LaravelMultiFactor\Actions\Fortify;

use Cybex\LaravelMultiFactor\Enums\MultiFactorAuthMode;
use Illuminate\Contracts\Auth\StatefulGuard;
use Laravel\Fortify\Actions\RedirectIfTwoFactorAuthenticatable;
use Laravel\Fortify\Events\TwoFactorAuthenticationChallenged;
use Laravel\Fortify\Fortify;
use Laravel\Fortify\LoginRateLimiter;
use MFA;

class RedirectIfMultiFactorAuthenticatable extends RedirectIfTwoFactorAuthenticatable
{
    public function __construct(StatefulGuard $guard, LoginRateLimiter $limiter)
    {
        parent::__construct($guard, $limiter);
    }

    public function handle($request, $next)
    {
        $user = $this->validateCredentials($request);

        if (Fortify::confirmsTwoFactorAuthentication() || !$user->multiFactorAuthMethods()->exists()) {
            if ($user->multiFactorAuthMethods()->exists()) {
                return $this->twoFactorChallengeResponse($request, $user);
            }

            if (!MultiFactorAuthMode::isOptionalMode()) {
                MFA::setLoginIdAndRemember($user, $request->boolean('remember'));
                MFA::setVerified();
                return redirect()->route('mfa.setup');
            }
        }

        return $next($request);
    }

    protected function twoFactorChallengeResponse($request, $user)
    {
        $request->session()->put([
            'login.id' => $user->getKey(),
            'login.remember' => $request->boolean('remember'),
        ]);

        TwoFactorAuthenticationChallenged::dispatch($user);

        return $request->wantsJson()
            ? response()->json(['multi_factor' => true])
            : redirect()->route('mfa.show');
    }
}
