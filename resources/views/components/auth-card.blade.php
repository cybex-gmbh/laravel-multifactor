<div {{ $attributes->merge(['class' => 'auth-card']) }}>
    <div class="header">
        @if(isset($header))
            {{ $header }}
        @else
            <h1>Multi-factor Authentication</h1>
        @endif
        {{ $subtext ?? '' }}
    </div>

    {{ $slot }}
</div>
