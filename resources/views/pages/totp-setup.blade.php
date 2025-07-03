<x-multi-factor::layout>
    <x-slot name="title">@lang('multi-factor::auth.title')</x-slot>

    <x-multi-factor::auth-card>
{{--        <x-slot name="subtitle">--}}
{{--            <p>@lang('multi-factor::auth.email_challenge.subtitle', ['authenticationMethod' => $authenticationMethod, 'email' => $user->email])</p>--}}
{{--        </x-slot>--}}

        <x-multi-factor::form :action="route('two-factor.enable')" id="fortify-totp">
            <x-multi-factor::button type="submit" class="text-sm" form="fortify-totp">
                {{ __('Enable Totp') }}
            </x-multi-factor::button>
        </x-multi-factor::form>

        @if (session('status') == 'two-factor-authentication-enabled')
            <div class="mb-4 font-medium text-sm">
                Please finish configuring two factor authentication below.
            </div>
        @endif

        @if (isset($user->two_factor_secret))
            {!! request()->user()->twoFactorQrCodeSvg() !!}
        @endif

        <a href="{{ route('mfa.method', $mfaMethod) }}">
            <x-multi-factor::button>
                {{ __('Next') }}
            </x-multi-factor::button>
        </a>

{{--        <div class="mfa-row mfa-flex-end">--}}
{{--            <p class="mfa-text-sm">@lang('multi-factor::auth.email_challenge.subtitle_resend', ['authenticationMethod' => $authenticationMethod])</p>--}}

{{--            <x-multi-factor::form :action="route('mfa.method.send', $mfaMethod)" id="resend-code-form">--}}
{{--                <x-multi-factor::button class="text-sm">@lang('multi-factor::button.resend_mfa', ['authenticationMethod' => $authenticationMethod])</x-multi-factor::button>--}}
{{--            </x-multi-factor::form>--}}

{{--            <x-multi-factor::button type="submit" class="text-sm" form="mfa-login">--}}
{{--                {{ __('Log in') }}--}}
{{--            </x-multi-factor::button>--}}
{{--        </div>--}}
    </x-multi-factor::auth-card>
</x-multi-factor::layout>
