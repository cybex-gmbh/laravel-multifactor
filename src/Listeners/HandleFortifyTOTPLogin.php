<?php

namespace Cybex\LaravelMultiFactor\Listeners;

use Illuminate\Auth\Events\Logout;
use Laravel\Fortify\Events\ValidTwoFactorAuthenticationCodeProvided;
use MFA;

class HandleFortifyTOTPLogin
{
    public function handle(ValidTwoFactorAuthenticationCodeProvided $event): void
    {
        MFA::setVerified();
    }
}
