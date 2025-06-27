<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <title>{{ $title }}</title>

    @include('laravel-multi-factor::partials.mfa-assets')
</head>

<body id="mfa-body">
    <div class="multi-factor-auth">
        {{ $slot }}
    </div>
</body>
</html>
