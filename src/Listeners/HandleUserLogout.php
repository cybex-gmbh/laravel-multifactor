<?php

namespace Cybex\LaravelMultiFactor\Listeners;

use Illuminate\Auth\Events\Logout;
use MFA;

class HandleUserLogout
{
    public function handle(Logout $event): void
    {
        $user = $event->user;

        if ($user) {
            MFA::clear();
        }
    }
}
