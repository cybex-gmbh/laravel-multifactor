<x-multi-factor::layout>
    <x-slot name="title">@lang('multi-factor::auth.title')</x-slot>

    <x-multi-factor::auth-card>
        <x-slot name="subtitle">
            {{--            <p>@lang('multi-factor::auth.email_challenge.subtitle', ['authenticationMethod' => $authenticationMethod, 'email' => $user->email])</p>--}}
        </x-slot>

        <x-multi-factor::form :action="route('mfa.store', $mfaMethod)" id="mfa-login">
            <x-multi-factor::form.input field="code" label="Authentication Code" type="text" required autofocus autocomplete="one-time-code"/>
        </x-multi-factor::form>

        <div class="mfa-row mfa-flex-end">
            <x-multi-factor::button type="submit" class="text-sm" form="mfa-login">
                {{ __('Log in') }}
            </x-multi-factor::button>
        </div>

        @if (session('status') == 'two-factor-authentication-confirmed')
            <div class="mb-4 font-medium text-sm">
                Two factor authentication confirmed and enabled successfully.
            </div>
        @endif
    </x-multi-factor::auth-card>
</x-multi-factor::layout>
