<x-guest-layout>
    <x-slot name="title">Login</x-slot>

    <x-auth-card>
        <form method="POST" action="{{ route('2fa.email.login') }}">
            @csrf

            <!-- Email Address -->
            <x-form.input id="email" field="email" label="E-Mail Address" type="email" required autofocus />

            <!-- Remember Me -->
            <div class="block mt-4">
                <label for="remember_me" class="inline-flex items-center">
                    <input id="remember_me" type="checkbox" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" name="remember">
                    <span class="ml-2 text-sm text-gray-600">{{ __('Remember me') }}</span>
                </label>
            </div>

            <div class="flex items-center justify-end mt-4">
                <x-form.button class="ml-3 btn-primary">
                    {{ __('Log in') }}
                </x-form.button>
            </div>
        </form>
    </x-auth-card>
</x-guest-layout>
