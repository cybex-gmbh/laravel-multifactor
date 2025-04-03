<x-app-layout>
    <x-slot name="title">Delete 2FA Method</x-slot>

    <x-auth-card>
        <x-slot name="header">
            <p>Choose one of these methods to delete</p>
        </x-slot>

        @foreach($userMethods as $method)
            <a class="btn flex-row" href="{{ route('2fa.method', $method) }}">
                <x-svg method="{{ $method }}"></x-svg>
                <p><strong>{{ ucfirst($method->value) }}</strong></p>
            </a>
        @endforeach
    </x-auth-card>
</x-app-layout>

