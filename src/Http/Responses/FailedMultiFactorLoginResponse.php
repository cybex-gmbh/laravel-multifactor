<?php

namespace Cybex\LaravelMultiFactor\Http\Responses;

use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Contracts\FailedTwoFactorLoginResponse as FailedTwoFactorLoginResponseContract;

class FailedMultiFactorLoginResponse implements FailedTwoFactorLoginResponseContract
{
    /**
     * Create an HTTP response that represents the object.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function toResponse($request)
    {
        $method = $request->route()->parameter('method');

        [$key, $message] = $request->filled('recovery_code')
            ? ['recovery_code', __(sprintf('The provided %s recovery code was invalid.', $method->value))]
            : ['code', __(sprintf('The provided %s code was invalid.', $method->value))];

        if ($request->wantsJson()) {
            throw ValidationException::withMessages([
                $key => [$message],
            ]);
        }

        return redirect()->back()->withErrors([$key => $message]);
    }
}
