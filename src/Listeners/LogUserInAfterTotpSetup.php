<?php

namespace Cybex\LaravelMultiFactor\Listeners;

use Laravel\Fortify\Events\TwoFactorAuthenticationConfirmed;
use MFA;

class LogUserInAfterTotpSetup
{
    public function handle(TwoFactorAuthenticationConfirmed $event): void
    {
        if (!MFA::isPersistentLogin()) {
            MFA::login();

            redirect(config('fortify.home'));
        }
    }
}
