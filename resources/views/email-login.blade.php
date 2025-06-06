<x-multi-factor::layout>
    <x-slot name="title">Login</x-slot>

    <x-multi-factor::auth-card>
        <x-multi-factor::form :action="route('mfa.email.login')">
            <x-multi-factor::form.input id="email" field="email" label="E-Mail Address" type="email" required autofocus/>

            <div>
                <label for="remember_me">
                    <input id="remember_me" type="checkbox" name="remember">
                    <span class="">{{ __('Remember me') }}</span>
                </label>
            </div>

            <div class="flex flex-row flex-end">
                <x-multi-factor::button type="submit">
                    {{ __('Log in') }}
                </x-multi-factor::button>
            </div>
        </x-multi-factor::form>
    </x-multi-factor::auth-card>
</x-multi-factor::layout>
