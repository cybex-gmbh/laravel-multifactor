<?php

namespace Cybex\LaravelMultiFactor\Http\Responses;

use Illuminate\Http\JsonResponse;
use Laravel\Fortify\Contracts\TwoFactorConfirmedResponse as TwoFactorConfirmedResponseContract;
use Laravel\Fortify\Fortify;
use MFA;

class MultiFactorTotpConfirmedResponse implements TwoFactorConfirmedResponseContract
{
    /**
     * Create an HTTP response that represents the object.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function toResponse($request)
    {
        return $request->wantsJson()
                    ? new JsonResponse('', 200)
                    : redirect(MFA::isPersistentLogin() ? config('fortify.home') : route('mfa.settings', MFA::getUser()))->with('status', Fortify::TWO_FACTOR_AUTHENTICATION_CONFIRMED);
    }
}
