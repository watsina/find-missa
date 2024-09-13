<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title >{{ config('app.name', 'Find Mass') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link rel="stylesheet" href="https://fonts.bunny.net/css2?family=Nunito:wght@400;600;700&display=swap">

        <!-- Scripts -->
        <script>
            @isset($auth)
                window.Auth = @json($auth)
            @endisset
        </script>
        @vite(['ui/app.js'])
    </head>
    <body class="tw-h-full tw-font-sans tw-antialiased">
        <div id="app"/>
    </body>
</html>
