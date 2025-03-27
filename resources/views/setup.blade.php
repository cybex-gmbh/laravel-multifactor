<x-guest-layout>
    <x-slot name="title">Login</x-slot>

    <x-auth-card>
        <div>
            <p class="text-sm text-center mt-2">Select a verification method</p>
            <div class="mt-4 flex justify-center gap-2 flex-col">
                @foreach($methods as $method)
                    <a class="btn" href="{{ route('2fa.setup.method', $method) }}">{{ $method->value }}</a>
                @endforeach
            </div>
        </div>
    </x-auth-card>
</x-guest-layout>
