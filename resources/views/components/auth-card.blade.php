<div {{ $attributes->class('auth-card') }}>
    <div class="header">
        {!! $header ?? '<h1>Multi-factor Authentication</h1>' !!}
        {{ $subtext ?? '' }}
    </div>

    {{ $slot }}
</div>
