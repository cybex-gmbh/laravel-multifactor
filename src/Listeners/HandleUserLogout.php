<?php

namespace CybexGmbh\LaravelTwoFactor\Listeners;

use CybexGmbh\LaravelTwoFactor\Enums\TwoFactorAuthSession;
use Illuminate\Auth\Events\Logout;

class HandleUserLogout
{
    /**
     * Handle the event.
     */
    public function handle(Logout $event): void
    {
        $user = $event->user;

        if ($user) {
            TwoFactorAuthSession::clear();
        }
    }
}