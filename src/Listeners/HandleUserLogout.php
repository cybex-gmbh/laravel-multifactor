<?php

namespace Cybex\LaravelMultiFactor\Listeners;

use Illuminate\Auth\Events\Logout;
use MFA;

class HandleUserLogout
{
    /**
     * Handle the event.
     */
    public function handle(Logout $event): void
    {
        $user = $event->user;

        if ($user) {
            MFA::clear();
        }
    }
}
