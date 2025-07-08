<?php

namespace Cybex\LaravelMultiFactor\Actions\Fortify;

use App\Models\User;
use Laravel\Fortify\Fortify;
use Laravel\Fortify\Http\Requests\LoginRequest;
use MFA;

class RedirectIfMultiFactorAuthenticatable
{
    public function __invoke(LoginRequest $request, $next)
    {
        $user = User::where(Fortify::username(), $request->input(Fortify::username()))->first();

        if ($user->multiFactorAuthMethods()->exists()) {
            MFA::setUserId($user);

            $request->session()->put([
                'login.id' => $user->getKey(),
                'login.remember' => $request->boolean('remember'),
            ]);

            return redirect()->route('mfa.show');
        }

        return $next($request);
    }
}