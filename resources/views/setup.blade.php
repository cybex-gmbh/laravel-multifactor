<x-multi-factor::layout>
    <x-slot name="title">Login</x-slot>

    <x-multi-factor::auth-card>
        <div>
            <p class="text-sm text-center margin-top-2">Select a verification method</p>
            <div class="mfa-method-list">
                @foreach($methods as $method)
                    <a class="btn" href="{{ route('mfa.setup.method', $method) }}">{{ $method->value }}</a>
                @endforeach
            </div>
        </div>
    </x-multi-factor::auth-card>
</x-multi-factor::layout>
