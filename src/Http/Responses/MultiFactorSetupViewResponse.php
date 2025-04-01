<?php

namespace CybexGmbh\LaravelTwoFactor\Http\Responses;

use CybexGmbh\LaravelTwoFactor\Contracts\MultiFactorSetupViewResponseContract;

class MultiFactorSetupViewResponse implements MultiFactorSetupViewResponseContract
{
    protected array $methods;

    public function __construct(array $methods)
    {
        $this->methods = $methods;
    }
    public function toResponse($request)
    {
        $methods = $this->methods;

        return view('laravel-two-factor::setup', compact('methods'));
    }
}