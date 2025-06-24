<?php

namespace CybexGmbh\LaravelMultiFactor\Http\Controllers;

use App\Models\User;
use CybexGmbh\LaravelMultiFactor\Contracts\MultiFactorChallengeViewResponseContract;
use CybexGmbh\LaravelMultiFactor\Contracts\MultiFactorChooseViewResponseContract;
use CybexGmbh\LaravelMultiFactor\Contracts\MultiFactorSettingsViewResponseContract;
use CybexGmbh\LaravelMultiFactor\Enums\MultiFactorAuthMethod;
use CybexGmbh\LaravelMultiFactor\Enums\MultiFactorAuthMode;
use CybexGmbh\LaravelMultiFactor\Enums\MultiFactorAuthSession;
use Illuminate\Foundation\Application;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;

class MultiFactorAuthController extends Controller
{
    /**
     * @return mixed
     */
    public function show(): mixed
    {
        $user = Auth::user();
        $userMethods = $user->getUserMethods();

        if (MultiFactorAuthMode::isForceMode()) {
            $forceMethod = MultiFactorAuthMethod::getForceMethod();

            if (!$forceMethod->isAllowed()) {
                abort(500);
            }

            if ($forceMethod->isUserMethod()) {
                return Redirect::route('mfa.method', ['method' => $forceMethod]);
            }
        }

        if (count($userMethods) === 1) {
            return Redirect::route('mfa.method', ['method' => Arr::first($userMethods)]);
        }

        return app(MultiFactorChooseViewResponseContract::class, $userMethods);
    }

    /**
     * @param MultiFactorAuthMethod $method
     * @return MultiFactorChallengeViewResponseContract
     */
    public function handleTwoFactorAuthMethod(MultiFactorAuthMethod $method): MultiFactorChallengeViewResponseContract
    {
        return match ($method) {
            MultiFactorAuthMethod::EMAIL => $method->getHandler()->authenticate(),
        };
    }

    /**
     * @param MultiFactorAuthMethod $method
     * @return RedirectResponse
     */
    public function send(MultiFactorAuthMethod $method): RedirectResponse
    {
        return $method->getHandler()->send();
    }

    /**
     * @param MultiFactorAuthMethod|null $method
     * @return RedirectResponse|MultiFactorChooseViewResponseContract
     */
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

    /**
     * @param MultiFactorAuthMethod $method
     * @param RedirectResponse|null $back
     * @return RedirectResponse
     */
    public function deleteTwoFactorAuthMethod(MultiFactorAuthMethod $method, RedirectResponse $back = null): RedirectResponse
    {
        Auth::user()->multiFactorAuthMethods()->where('type', $method)->delete();

        return $back ?? redirect()->back();
    }

    /**
     * @param Request $request
     * @param MultiFactorAuthMethod $method
     * @param User|null $user
     * @param int|null $code
     * @return Application|Redirector|RedirectResponse
     */
    public function verifyTwoFactorAuthCode(Request $request, MultiFactorAuthMethod $method, User $user = null, int $code = null): Application|Redirector|RedirectResponse
    {
        $code ??= $request->integer('code') ?? abort(403);

        if (MultiFactorAuthSession::isCodeExpired() || $code !== MultiFactorAuthSession::getCode()) {
            abort(403);
        }

        if (!$method->isUserMethod()) {
            return $method->getHandler()->setup();
        }

        MultiFactorAuthSession::clear();
        MultiFactorAuthSession::VERIFIED->put();

        return Redirect::intended();
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function authenticateByEmailOnly(Request $request): RedirectResponse
    {
        $user = User::whereEmail($request->input('email'))->first();

        if (!$user) {
            return Redirect::back()->withErrors(['email' => __('auth.failed')]);
        }

        Auth::login($user);

        return Redirect::intended();
    }

    /**
     * @param User $user
     * @return MultiFactorSettingsViewResponseContract|Application|mixed|void
     */
    public function twoFactorSettings(User $user)
    {
        if (Auth::user()->is($user) && !MultiFactorAuthMode::isForceMode()) {
            return app(MultiFactorSettingsViewResponseContract::class, [$user]);
        }

         abort(403);
    }
}
