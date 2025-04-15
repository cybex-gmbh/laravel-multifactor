<?php

namespace CybexGmbh\LaravelMultiFactor\Http\Responses;

use CybexGmbh\LaravelMultiFactor\Contracts\MultiFactorSetupViewResponseContract;

class MultiFactorSetupViewResponse implements MultiFactorSetupViewResponseContract
{
    public function __construct(protected array $methods)
    {}

    public function toResponse($request)
    {
        $methods = $this->methods;

        return view('laravel-multi-factor::setup', compact('methods'));
    }
}
