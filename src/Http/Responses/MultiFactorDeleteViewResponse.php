<?php

namespace CybexGmbh\LaravelTwoFactor\Http\Responses;

use CybexGmbh\LaravelTwoFactor\Contracts\MultiFactorDeleteViewResponseContract;
use Illuminate\Http\RedirectResponse;

class MultiFactorDeleteViewResponse implements MultiFactorDeleteViewResponseContract
{
    protected array $userMethods;
    protected RedirectResponse $back;

    public function __construct(array $methods, RedirectResponse $back)
    {
        $this->userMethods = $methods;
        $this->back = $back;
    }

    public function toResponse($request)
    {
        $userMethods = $this->userMethods;
        $back = $this->back;

        return view('laravel-two-factor::delete-choose', compact('userMethods', 'back'));
    }
}