<?php

namespace Cybex\LaravelMultiFactor\Http\Responses;

use Cybex\LaravelMultiFactor\Contracts\MultiFactorLoginViewResponseContract;
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
        return view('laravel-multi-factor::pages.email-login');
    }
}
