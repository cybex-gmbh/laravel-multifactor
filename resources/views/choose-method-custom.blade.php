<x-app-layout>
    <x-slot name="title">Choose 2FA Method</x-slot>

    <div class="container">
        <div class="center">
            <h1 class="align-center">Multi-factor Authentication</h1>
            <p class="align-center">Choose one of these methods to log in</p>
            @foreach($userMethods as $method)
                <a class="btn" href="{{ route('2fa.method', $method) }}">
                <div class="flex-container">
                    <x-svg method="{{ $method }}"></x-svg>
                    <p><strong>{{ ucfirst($method->value) }}</strong></p>
                </div>
                </a>
            @endforeach
        </div>
    </div>
</x-app-layout>
