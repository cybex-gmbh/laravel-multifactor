<x-app-layout>
    <x-slot name="title">Choose 2FA Method</x-slot>

    <x-auth-card>
        <x-slot name="header">
            <p>Choose one of these methods to log in</p>
        </x-slot>

        @foreach($userMethods as $method)
            <a class="flex-row" href="{{ route('2fa.method', $method) }}">
                <x-svg method="{{ $method }}"></x-svg>
                <p><strong>{{ ucfirst($method->value) }}</strong></p>
            </a>
        @endforeach
    </x-auth-card>
</x-app-layout>
