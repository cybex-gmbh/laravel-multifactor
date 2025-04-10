<?php

namespace CybexGmbh\LaravelTwoFactor\Http\Responses;

use CybexGmbh\LaravelTwoFactor\Contracts\MultiFactorSetupViewResponseContract;

class MultiFactorSetupViewResponse implements MultiFactorSetupViewResponseContract
{
    public function __construct(protected array $methods)
    {}

    public function toResponse($request)
    {
        $methods = $this->methods;

        return view('laravel-two-factor::setup', compact('methods'));
    }
}