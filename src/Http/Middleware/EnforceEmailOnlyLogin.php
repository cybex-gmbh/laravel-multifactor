<?php

namespace Cybex\LaravelMultiFactor\Http\Middleware;

use Closure;
use Cybex\LaravelMultiFactor\Contracts\MultiFactorLoginViewResponseContract;
use Illuminate\Http\Request;

class EnforceEmailOnlyLogin
{
    public function handle(Request $request, Closure $next)
    {
        $response = app(MultiFactorLoginViewResponseContract::class);
        return response($response->toResponse($request));
    }
}
