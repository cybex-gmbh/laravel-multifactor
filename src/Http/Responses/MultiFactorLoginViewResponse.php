<?php

namespace CybexGmbh\LaravelMultiFactor\Http\Responses;

use CybexGmbh\LaravelMultiFactor\Contracts\MultiFactorLoginViewResponseContract;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Application;

class MultiFactorLoginViewResponse implements MultiFactorLoginViewResponseContract
{
    /**
     * @param $request
     * @return Application|Factory|object|View
     */
    public function toResponse($request)
    {
        return view('laravel-multi-factor::email-login');
    }
}
