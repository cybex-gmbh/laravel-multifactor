<x-multi-factor-layout>
    <x-slot name="title">Login</x-slot>

    <x-multi-factor-auth-card>
        <form method="POST" action="{{ route('mfa.email.login') }}">
            @csrf

            <!-- Email Address -->
            <x-form.input id="email" field="email" label="E-Mail Address" type="email" required autofocus />

            <!-- Remember Me -->
            <div>
                <label for="remember_me">
                    <input id="remember_me" type="checkbox" name="remember">
                    <span class="">{{ __('Remember me') }}</span>
                </label>
            </div>

            <div class="flex-row-end">
                <x-form.button class="button">
                    {{ __('Log in') }}
                </x-form.button>
            </div>
        </form>
    </x-multi-factor-auth-card>
</x-multi-factor-layout>
