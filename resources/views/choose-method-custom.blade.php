<x-app-layout>
    <x-slot name="title">Choose 2FA Method</x-slot>

    <div class="container">
        <div class="main">
            <h1>Multi-factor Authentication</h1>
            <p>Choose one of these methods to log in</p>
            @foreach($userMethods as $method)
                <div>
                    <a class="btn" href="{{ route('2fa.method', $method) }}">
                        <div class="flex-container">
                            <x-svg method="{{ $method }}"></x-svg>
                            <p><strong>{{ ucfirst($method->value) }}</strong></p>
                        </div>
                    </a>
                </div>
            @endforeach
        </div>
    </div>
</x-app-layout>
