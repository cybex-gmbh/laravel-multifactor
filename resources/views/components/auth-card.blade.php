<div {{ $attributes->class('auth-card') }}>
    <div class="header">
        {!! $header ?? sprintf('<h1>%s</h1>', __('multi-factor::auth.title')) !!}
        {{ $subtitle ?? '' }}
    </div>

    {{ $slot }}
</div>
