<?php

namespace CybexGmbh\LaravelTwoFactor\Http\Responses;

use CybexGmbh\LaravelTwoFactor\Contracts\MultiFactorDeleteViewResponseContract;
use Illuminate\Http\RedirectResponse;

class MultiFactorDeleteViewResponse implements MultiFactorDeleteViewResponseContract
{
    protected array $methods;
    protected RedirectResponse $back;

    public function __construct(array $methods, RedirectResponse $back)
    {
        $this->methods = $methods;
        $this->back = $back;
    }

    public function toResponse($request)
    {
        $methods = $this->methods;
        $back = $this->back;

        return view('laravel-two-factor::delete-choose', compact('methods', 'back'));
    }
}