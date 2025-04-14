<x-multi-factor-layout>
    <x-slot name="title">Login</x-slot>

    <x-multi-factor-auth-card>
        <div>
            <p class="text-sm text-center mt-2">Select a verification method</p>
            <div class="mt-4 flex justify-center gap-2 flex-col">
                @foreach($methods as $method)
                    <a class="btn" href="{{ route('mfa.setup.method', $method) }}">{{ $method->value }}</a>
                @endforeach
            </div>
        </div>
    </x-multi-factor-auth-card>
</x-multi-factor-layout>
