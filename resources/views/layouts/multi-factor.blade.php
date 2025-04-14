<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Multi-Factor | {{ $title }}</title>

    @vite('resources/css/multi-factor.css', 'vendor/laravel-multi-factor/build')
</head>

<body>
<div class="multi-factor-main">
    {{ $slot }}
</div>
</body>
</html>
