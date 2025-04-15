<?php

namespace CybexGmbh\LaravelMultiFactor\Http\Responses;

use CybexGmbh\LaravelMultiFactor\Contracts\MultiFactorChooseViewResponseContract;
use CybexGmbh\LaravelMultiFactor\Enums\MultiFactorAuthSession;

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
        $isVerified = MultiFactorAuthSession::VERIFIED->get();

        return view('laravel-multi-factor::choose-method', compact(['userMethods', 'isVerified']));
    }
}
