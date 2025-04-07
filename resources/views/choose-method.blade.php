<x-two-factor-layout>
    <x-slot name="title">Choose 2FA Method</x-slot>

    <x-multi-factor-auth-card>
        <x-slot name="subtext">
            <p>Choose one of these methods to log in</p>
        </x-slot>

        @foreach($userMethods as $method)
            <a class="link flex-row underline" href="{{ route('2fa.method', $method) }}">
                <x-svg method="{{ $method }}"></x-svg>
                <p><strong>{{ ucfirst($method->value) }}</strong></p>
            </a>
        @endforeach
    </x-multi-factor-auth-card>
</x-two-factor-layout>
