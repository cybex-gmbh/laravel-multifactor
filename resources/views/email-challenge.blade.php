<x-app-layout>
    <x-slot name="title">Two Factor Login</x-slot>

    <x-auth-card>
        <x-slot name="header">
            <p>An email with an authentication link was just sent to <strong>{{ $user->email }}</strong></p>
        </x-slot>

        <form method="POST" action="{{ route('2fa.verify', $method, $user) }}" id="2fa-login" class="mt-4">
            @csrf
            <div>
                <x-form.input id="code" field="code" label="Authentication Code" type="number" required autofocus autocomplete="one-time-code" />
            </div>
        </form>

        <div class="flex-center-end">
            <div class="text-small-gray">
                Didn't receive your link?
                <form id="resend-code-form" action="{{ route('2fa.method.send', $method) }}" method="POST" class="underline-inline-primary">
                    @csrf
                    <button>{{ __('Resend Link') }}</button>
                </form>
            </div>

            <x-form.button class="margin-left-3 btn-primary" form="2fa-login">
                {{ __('Log in') }}
            </x-form.button>
        </div>
    </x-auth-card>
</x-app-layout>
