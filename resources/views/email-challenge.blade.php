<x-guest-layout>
    <x-slot name="title">Two Factor Login</x-slot>

    <x-auth-card>
        <div>
            <p class="text-sm text-center mt-2">An email with an authentication link was just sent to <strong>{{ $user->email }}</strong></p>

            <form method="POST" action="{{ route('2fa.verify', $method, $user) }}" id="2fa-login" class="mt-4">
                @csrf
                <div>
                    <x-form.input id="code" field="code" label="Authentication Code" type="number" required autofocus autocomplete="one-time-code" />
                </div>
            </form>

            <div class="flex items-center justify-end mt-4">
                <div class="text-sm text-gray-600 hover:text-gray-900">
                    Didn't receive your link?
                    <form id="resend-code-form" action="{{ route('2fa.method.send', $method) }}" method="POST" class="underline inline text-primary-darker">
                        @csrf
                        <button>{{ __('Resend Link') }}</button>
                    </form>
                </div>

                <x-form.button class="ml-3 btn-primary" form="2fa-login">
                    {{ __('Log in') }}
                </x-form.button>
            </div>
        </div>
    </x-auth-card>
</x-guest-layout>
