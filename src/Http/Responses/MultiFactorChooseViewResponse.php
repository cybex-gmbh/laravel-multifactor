<?php

namespace Cybex\LaravelMultiFactor\Http\Responses;

use Cybex\LaravelMultiFactor\Contracts\MultiFactorChooseViewResponseContract;
use MFA;

class MultiFactorChooseViewResponse implements MultiFactorChooseViewResponseContract
{
    protected array $userMethods;

    public function __construct(array $userMethods)
    {
        $this->userMethods = $userMethods;
    }

    public function toResponse($request)
    {
        $userMethods = $this->userMethods;
        $isVerified = MFA::isVerified();

        return view('laravel-multi-factor::pages.choose-method', compact(['userMethods', 'isVerified']));
    }
}
