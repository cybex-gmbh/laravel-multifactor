<?php

namespace CybexGmbh\LaravelTwoFactor\Http\Controllers;

use App\Models\User;
use CybexGmbh\LaravelTwoFactor\Contracts\MultiFactorChallengeViewResponseContract;
use CybexGmbh\LaravelTwoFactor\Contracts\MultiFactorChooseViewResponseContract;
use CybexGmbh\LaravelTwoFactor\Contracts\MultiFactorDeleteViewResponseContract;
use CybexGmbh\LaravelTwoFactor\Contracts\MultiFactorSettingsViewResponseContract;
use CybexGmbh\LaravelTwoFactor\Contracts\MultiFactorSetupViewResponseContract;
use CybexGmbh\LaravelTwoFactor\Enums\TwoFactorAuthMethod;
use CybexGmbh\LaravelTwoFactor\Enums\TwoFactorAuthMode;
use CybexGmbh\LaravelTwoFactor\Enums\TwoFactorAuthSession;
use Illuminate\Foundation\Application;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;

use Symfony\Component\HttpKernel\Exception\HttpException;

class TwoFactorAuthController extends Controller
{
    public function show(): mixed
    {
        $user = Auth::user();
        $userMethods = $user->getTwoFactorAuthMethods();
        $configuredMode = TwoFactorAuthMode::fromConfig();

        switch ($configuredMode) {
            case TwoFactorAuthMode::FORCE:
                $forceMethod = TwoFactorAuthMethod::getForceMethod();

                if ($forceMethod->isUserMethod()) {
                    return Redirect::route('2fa.method', ['method' => $forceMethod]);
                }
                break;

            case TwoFactorAuthMode::OPTIONAL:
                if (!count(array_intersect($user->getTwoFactorAuthMethodsNames(), TwoFactorAuthMethod::getAllowedMethodsNames()))) {
                    $methodsToSetup = array_diff(TwoFactorAuthMethod::getAllowedMethodsNames(), $user->getTwoFactorAuthMethodsNames());

                    // refactor this, to complicated
                    return app(MultiFactorChooseViewResponseContract::class, [array_map(fn($method) => TwoFactorAuthMethod::from($method), $methodsToSetup)]);
                }
                break;
        }

        if (count($userMethods) === 1) {
            return Redirect::route('2fa.method', ['method' => $userMethods[0]]);
        }

        return app(MultiFactorChooseViewResponseContract::class, [$userMethods]);
    }

    public function handleTwoFactorAuthMethod(TwoFactorAuthMethod $method): MultiFactorChallengeViewResponseContract
    {
        return match ($method) {
            TwoFactorAuthMethod::EMAIL => $method->getHandler()->authenticate(),
            TwoFactorAuthMethod::TOTP => TwoFactorAuthMethod::EMAIL->getHandler()->authenticate(),
        };
    }

    public function send(TwoFactorAuthMethod $method): RedirectResponse
    {
        return $method->getHandler()->send();
    }

    public function setup(TwoFactorAuthMethod $method = null): RedirectResponse|MultiFactorSetupViewResponseContract|MultiFactorChooseViewResponseContract
    {
        $mode = TwoFactorAuthMode::fromConfig();
        $forceMethod = TwoFactorAuthMethod::getForceMethod();
        $method = $method?->isAllowed() ? [$method] : null;
        $methods = $method ?? TwoFactorAuthMethod::getAllowedMethods();

        if ($mode === TwoFactorAuthMode::FORCE) {
            return Redirect::route('2fa.method', ['method' => $forceMethod]);
        }

        if (count($methods) === 1) {
            return Redirect::route('2fa.method', ['method' => $methods[0]]);
        }

        // either make this to array with [] brackets or remove the array deconstruction ... in the service provider for the choose view
        return app(MultiFactorChooseViewResponseContract::class, [$methods]);
    }

    public function handleDeletion(): mixed
    {
        $methods = Auth::user()->getTwoFactorAuthMethods();
        $back = Redirect::back();

        if (count($methods) === 1) {
            return $this->deleteTwoFactorAuthMethod($methods[0], $back);
        }

        return app(MultiFactorDeleteViewResponseContract::class, compact('methods', 'back'));
    }

    public function deleteTwoFactorAuthMethod(TwoFactorAuthMethod $method, RedirectResponse $back = null): RedirectResponse
    {
        Auth::user()->twoFactorAuthMethods()->where('type', $method)->delete();

        return $back ?? redirect()->back();
    }

    public function verifyTwoFactorAuthCode(Request $request, TwoFactorAuthMethod $method, User $user = null, int $code = null): Application|Redirector|RedirectResponse
    {
        $code ??= $request->integer('code') ?? throw new HttpException(403);

        if ($code !== TwoFactorAuthSession::CODE->get()) {
            abort(403);
        }

        if (!$method->isUserMethod()) {
            $redirect = $method->getHandler()->setup();

            array_map(fn($method) => $this->deleteTwoFactorAuthMethod(TwoFactorAuthMethod::from($method)), Auth::user()->getUnallowedMethodsNames());
        }

        TwoFactorAuthSession::clear();
        TwoFactorAuthSession::VERIFIED->put();

        return $redirect ?? Redirect::intended();
    }

    public function emailLogin(Request $request): RedirectResponse
    {
        $user = User::whereEmail($request->input('email'))->first();

        if (!$user) {
            return Redirect::back()->withErrors(['email' => __('auth.failed')]);
        }

        Auth::login($user);

        return Redirect::intended();
    }

    public function twoFactorSettings(User $user)
    {
        if (Auth::user()->is($user) && TwoFactorAuthMode::fromConfig() === TwoFactorAuthMode::OPTIONAL) {
            return app(MultiFactorSettingsViewResponseContract::class, [$user]);
        }

         abort(403);
    }
}
