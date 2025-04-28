<?php

namespace CybexGmbh\LaravelMultiFactor\Http\Responses;

use CybexGmbh\LaravelMultiFactor\Contracts\MultiFactorSetupViewResponseContract;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Application;

class MultiFactorSetupViewResponse implements MultiFactorSetupViewResponseContract
{
    /**
     * @param array $methods
     */
    public function __construct(protected array $methods)
    {}

    /**
     * @param $request
     * @return Application|Factory|View
     */
    public function toResponse($request)
    {
        $methods = $this->methods;

        return view('laravel-multi-factor::setup', compact('methods'));
    }
}
