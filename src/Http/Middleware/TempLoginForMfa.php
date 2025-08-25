<?php

namespace Cybex\LaravelMultiFactor\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use MFA;

class TempLoginForMfa
{
    public function handle(Request $request, Closure $next)
    {
        if (!auth()->check() && session()->has('login.id') && MFA::validateSecret()) {
            Auth::onceUsingId(MFA::getUser()->getKey());
        }

        return $next($request);
    }
}
