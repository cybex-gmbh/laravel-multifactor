<x-multi-factor-layout>
    <x-slot name="title">Two Factor Login</x-slot>

    <x-multi-factor-auth-card>
        <x-slot name="subtext">
            <p>An email with an authentication {{ $authenticationMethod }} was just sent to <strong>{{ $user->email }}</strong></p>
        </x-slot>

        <form method="POST" action="{{ route('mfa.verify', $mfaMethod, $user) }}" id="2fa-login" class="mt-4">
            @csrf
            <div>
                <x-form.input id="code" field="code" label="Authentication Code" type="text" required autofocus autocomplete="one-time-code" />
            </div>
        </form>

        <div class="flex flex-row flex-end">
            <p class="text-sm">Didn't receive your {{ $authenticationMethod }}?</p>
            <div>
                <form id="resend-code-form" action="{{ route('mfa.method.send', $mfaMethod) }}" method="POST" class="underline-inline-primary">
                    @csrf
                    <button class="button text-sm">{{ __(sprintf('Resend %s', $authenticationMethod)) }}</button>
                </form>
            </div>

            <x-form.button class="button text-sm" form="2fa-login">
                {{ __('Log in') }}
            </x-form.button>
        </div>
    </x-multi-factor-auth-card>
</x-multi-factor-layout>
