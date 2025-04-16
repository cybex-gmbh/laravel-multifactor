<?php

namespace CybexGmbh\LaravelMultiFactor\Http\Controllers;

use App\Models\User;
use CybexGmbh\LaravelMultiFactor\Contracts\MultiFactorChallengeViewResponseContract;
use CybexGmbh\LaravelMultiFactor\Contracts\MultiFactorChooseViewResponseContract;
use CybexGmbh\LaravelMultiFactor\Contracts\MultiFactorDeleteViewResponseContract;
use CybexGmbh\LaravelMultiFactor\Contracts\MultiFactorSettingsViewResponseContract;
use CybexGmbh\LaravelMultiFactor\Contracts\MultiFactorSetupViewResponseContract;
use CybexGmbh\LaravelMultiFactor\Enums\MultiFactorAuthMethod;
use CybexGmbh\LaravelMultiFactor\Enums\MultiFactorAuthMode;
use CybexGmbh\LaravelMultiFactor\Enums\MultiFactorAuthSession;
use Illuminate\Foundation\Application;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Symfony\Component\HttpKernel\Exception\HttpException;

class MultiFactorAuthController extends Controller
{
    public function show(): mixed
    {
        $user = Auth::user();
        $userMethods = $user->getMultiFactorAuthMethods();
        $configuredMode = MultiFactorAuthMode::fromConfig();

        switch ($configuredMode) {
            case MultiFactorAuthMode::FORCE:
                $forceMethod = MultiFactorAuthMethod::getForceMethod();

                if ($forceMethod->isUserMethod()) {
                    return Redirect::route('mfa.method', ['method' => $forceMethod]);
                }
                break;

            case MultiFactorAuthMode::OPTIONAL:
                if (!count(array_intersect($user->getMultiFactorAuthMethodsNames(), MultiFactorAuthMethod::getAllowedMethodsNames()))) {
                    return app(MultiFactorChooseViewResponseContract::class, MultiFactorAuthMethod::getMethodsByNames($user->getRemainingAllowedMethodsNames()));
                }
                break;
        }

        if (count($userMethods) === 1) {
            return Redirect::route('mfa.method', ['method' => $userMethods[0]]);
        }

        return app(MultiFactorChooseViewResponseContract::class, $userMethods);
    }

    public function handleTwoFactorAuthMethod(MultiFactorAuthMethod $method): MultiFactorChallengeViewResponseContract
    {
        return match ($method) {
            MultiFactorAuthMethod::EMAIL => $method->getHandler()->authenticate(),
            MultiFactorAuthMethod::TOTP => MultiFactorAuthMethod::EMAIL->getHandler()->authenticate(),
        };
    }

    public function send(MultiFactorAuthMethod $method): RedirectResponse
    {
        return $method->getHandler()->send();
    }

    public function setup(MultiFactorAuthMethod $method = null): RedirectResponse|MultiFactorSetupViewResponseContract|MultiFactorChooseViewResponseContract
    {
        $mode = MultiFactorAuthMode::fromConfig();
        $forceMethod = MultiFactorAuthMethod::getForceMethod();
        $method = $method?->isAllowed() ? [$method] : null;
        $methods = $method ?? MultiFactorAuthMethod::getAllowedMethods();

        if ($mode === MultiFactorAuthMode::FORCE) {
            return Redirect::route('mfa.method', ['method' => $forceMethod]);
        }

        if (count($methods) === 1) {
            return Redirect::route('mfa.method', ['method' => $methods[0]]);
        }

        // either make this to array with [] brackets or remove the array deconstruction ... in the service provider for the choose view
        return app(MultiFactorChooseViewResponseContract::class, $methods);
    }

    public function handleDeletion(): mixed
    {
        $methods = Auth::user()->getMultiFactorAuthMethods();
        $back = Redirect::back();

        if (count($methods) === 1) {
            return $this->deleteTwoFactorAuthMethod($methods[0], $back);
        }

        return app(MultiFactorDeleteViewResponseContract::class, compact('methods', 'back'));
    }

    public function deleteTwoFactorAuthMethod(MultiFactorAuthMethod $method, RedirectResponse $back = null): RedirectResponse
    {
        Auth::user()->multiFactorAuthMethods()->where('type', $method)->delete();

        return $back ?? redirect()->back();
    }

    public function verifyTwoFactorAuthCode(Request $request, MultiFactorAuthMethod $method, User $user = null, int $code = null): Application|Redirector|RedirectResponse
    {
        $code ??= $request->integer('code') ?? throw new HttpException(403);

        if (MultiFactorAuthSession::isCodeExpired() && $code !== MultiFactorAuthSession::getCode()) {
            abort(403);
        }

        if (!$method->isUserMethod()) {
            $redirect = $method->getHandler()->setup();

            array_map(fn($method) => $this->deleteTwoFactorAuthMethod(MultiFactorAuthMethod::from($method)), Auth::user()->getUnallowedMethodsNames());
        }

        MultiFactorAuthSession::clear();
        MultiFactorAuthSession::VERIFIED->put();

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
        if (Auth::user()->is($user) && MultiFactorAuthMode::fromConfig() !== MultiFactorAuthMode::FORCE) {
            return app(MultiFactorSettingsViewResponseContract::class, [$user]);
        }

         abort(403);
    }
}
