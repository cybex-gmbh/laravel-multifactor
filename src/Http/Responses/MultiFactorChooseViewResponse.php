<?php

namespace CybexGmbh\LaravelMultiFactor\Http\Responses;

use CybexGmbh\LaravelMultiFactor\Contracts\MultiFactorChooseViewResponseContract;
use CybexGmbh\LaravelMultiFactor\Enums\MultiFactorAuthSession;

class MultiFactorChooseViewResponse implements MultiFactorChooseViewResponseContract
{
    protected array $userMethods;

    /**
     * @param array $userMethods
     */
    public function __construct(array $userMethods)
    {
        $this->userMethods = $userMethods;
    }

    /**
     * @param $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application|object|\Symfony\Component\HttpFoundation\Response
     */
    public function toResponse($request)
    {
        $userMethods = $this->userMethods;
        $isVerified = MultiFactorAuthSession::VERIFIED->get();

        return view('laravel-multi-factor::pages.choose-method', compact(['userMethods', 'isVerified']));
    }
}
