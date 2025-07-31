<?php

namespace Cybex\LaravelMultiFactor\Listeners;

use MFA;

class MarkUserAsVerifiedAfterLogin
{
    public function handle(): void
    {
        MFA::setVerified();
    }
}
