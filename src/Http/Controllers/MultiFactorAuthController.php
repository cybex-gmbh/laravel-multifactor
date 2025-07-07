<?php

namespace Cybex\LaravelMultiFactor\Http\Controllers;

use App\Models\User;
use Cybex\LaravelMultiFactor\Contracts\MultiFactorChallengeViewResponseContract;
use Cybex\LaravelMultiFactor\Contracts\MultiFactorChooseViewResponseContract;
use Cybex\LaravelMultiFactor\Contracts\MultiFactorSettingsViewResponseContract;
use Cybex\LaravelMultiFactor\Enums\MultiFactorAuthMethod;
use Cybex\LaravelMultiFactor\Enums\MultiFactorAuthMode;
use Illuminate\Foundation\Application;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use MFA;

class MultiFactorAuthController extends Controller
{
    public function show(): mixed
    {
        $user = Auth::user();
        $userMethods = $user->getUserMethods();

        if (MultiFactorAuthMode::isForceMode()) {
            $forceMethod = MultiFactorAuthMethod::getForceMethod();

            if ($forceMethod->isUserMethod()) {
                return Redirect::route('mfa.method', ['method' => $forceMethod]);
            }
        }

        if (count($userMethods) === 1) {
            return Redirect::route('mfa.method', ['method' => Arr::first($userMethods)]);
        }

        return app(MultiFactorChooseViewResponseContract::class, $userMethods ?: MultiFactorAuthMethod::getAllowedMethods());
    }

    public function handleMultiFactorAuthMethod(MultiFactorAuthMethod $method): MultiFactorChallengeViewResponseContract
    {
        return match ($method) {
            MultiFactorAuthMethod::EMAIL => $method->getHandler()->challenge(),
        };
    }

    public function send(MultiFactorAuthMethod $method): RedirectResponse
    {
        return match ($method) {
            MultiFactorAuthMethod::EMAIL => $method->getHandler()->sendEmail(),
        };
    }

    public function setup(MultiFactorAuthMethod $method = null): RedirectResponse|MultiFactorChooseViewResponseContract
    {
        $methods = $method?->isAllowed() ? [$method] : MultiFactorAuthMethod::getAllowedMethods();

        if (MultiFactorAuthMode::isForceMode()) {
            return Redirect::route('mfa.method', ['method' => MultiFactorAuthMethod::getForceMethod()]);
        }

        if (count($methods) === 1) {
            return Redirect::route('mfa.method', ['method' => Arr::first($methods)]);
        }

        return app(MultiFactorChooseViewResponseContract::class, $methods);
    }

    public function deleteMultiFactorAuthMethod(MultiFactorAuthMethod $method, RedirectResponse $back = null): RedirectResponse
    {
        Auth::user()->multiFactorAuthMethods()->where('type', $method)->detach();

        return $back ?? redirect()->back();
    }

    public function verifyTwoFactorAuthCode(Request $request, MultiFactorAuthMethod $method, User $user = null, int $code = null): Application|Redirector|RedirectResponse
    {
        $code ??= $request->integer('code') ?? abort(403);

        if (MFA::isCodeExpired() || MFA::getCode() !== $code) {
            abort(403);
        }

        if (!$method->isUserMethod()) {
            return $method->getHandler()->setup();
        }

        MFA::clear();
        MFA::setVerified();

        return Redirect::intended();
    }

    public function authenticateByEmailOnly(Request $request): RedirectResponse
    {
        $user = User::whereEmail($request->input('email'))->first();

        if (!$user) {
            return Redirect::back()->withErrors(['email' => __('auth.failed')]);
        }

        Auth::login($user);

        return Redirect::intended();
    }

    public function multiFactorSettings(User $user)
    {
        if (Auth::user()->is($user) && !MultiFactorAuthMode::isForceMode()) {
            return app(MultiFactorSettingsViewResponseContract::class, [$user]);
        }

         abort(403);
    }
}
