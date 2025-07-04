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
            <div style="display: flex; flex-direction: column; justify-content: center; align-items: center;">
                <p>Scan the QR code below using an authenticator app</p>
                {!! request()->user()->twoFactorQrCodeSvg() !!}
            </div>
        @endif

        @if (session('status') == 'two-factor-authentication-confirmed')
            <div class="mb-4 font-medium text-sm">
                Two factor authentication confirmed and enabled successfully.
            </div>
        @endif

        <div class="mfa-row mfa-flex-end">
            <a href="{{ route('mfa.method', $mfaMethod) }}">
                <x-multi-factor::button>
                    {{ __('Next') }}
                </x-multi-factor::button>
            </a>
        </div>
    </x-multi-factor::auth-card>
</x-multi-factor::layout>
