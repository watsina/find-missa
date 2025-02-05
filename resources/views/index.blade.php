<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="tw-h-full tw-bg-gray-100">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title >{{ config('app.name', 'Find Mass') }}</title>

        <link rel="manifest" href="/site.webmanifest">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link rel="stylesheet" href="https://fonts.bunny.net/css2?family=Nunito:wght@400;600;700&display=swap">

        @if(!App::environment('production'))
            <meta name="robots" content="noindex">
        @endif
        <!-- Scripts -->
        <script>
            @isset($auth)
                window.Auth = @json($auth)
            @endisset
        </script>
        @vite(['ui/app.js'])
    </head>
    <body class="tw-font-sans tw-antialiased">
        <div id="app"/>
    </body>
</html>
