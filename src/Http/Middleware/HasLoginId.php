<?php

namespace Cybex\LaravelMultiFactor\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use MFA;

class HasLoginId
{
    public function handle(Request $request, Closure $next)
    {
        if (!auth()->check() && !session()->has('login.id') && !MFA::isVerified()) {
            return redirect()->route('login');
        }

        return $next($request);
    }
}
