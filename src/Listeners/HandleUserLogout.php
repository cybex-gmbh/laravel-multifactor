<?php

namespace CybexGmbh\LaravelMultiFactor\Listeners;

use CybexGmbh\LaravelMultiFactor\Enums\MultiFactorAuthSession;
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
            MultiFactorAuthSession::clear();
        }
    }
}