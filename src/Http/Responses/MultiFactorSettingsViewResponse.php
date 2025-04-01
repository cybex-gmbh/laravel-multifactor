<?php

namespace CybexGmbh\LaravelTwoFactor\Http\Responses;

use CybexGmbh\LaravelTwoFactor\Contracts\MultiFactorSettingsViewResponseContract;
use Illuminate\Foundation\Auth\User;

class MultiFactorSettingsViewResponse implements MultiFactorSettingsViewResponseContract
{
    protected User $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }
    public function toResponse($request)
    {
        $user = $this->user;

        return view('laravel-two-factor::settings', compact('user'));
    }
}