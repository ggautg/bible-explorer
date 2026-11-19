<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="getsemani" class="dark">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        {{-- Aplica el tema bíblico guardado antes de pintar para evitar parpadeos. --}}
        <script>
            (function () {
                var themes = ['pergamino','olivo','jordan','getsemani','sinai','patmos'];
                var dark = ['getsemani','sinai','patmos'];
                var theme = 'getsemani';
                try {
                    var stored = localStorage.getItem('theme') || localStorage.getItem('appearance');
                    var m = document.cookie.match(/(?:^|; )(?:theme|appearance)=([^;]+)/);
                    var cookie = m ? decodeURIComponent(m[1]) : null;
                    var v = stored || cookie || 'getsemani';
                    if (v === 'light') v = 'pergamino';
                    if (v === 'dark' || v === 'system') v = 'getsemani';
                    if (themes.indexOf(v) !== -1) theme = v;
                } catch (e) {}
                var isDark = dark.indexOf(theme) !== -1;
                var root = document.documentElement;
                root.setAttribute('data-theme', theme);
                root.classList.toggle('dark', isDark);
                root.style.colorScheme = isDark ? 'dark' : 'light';
            })();
        </script>

        <style>
            html, body {
                background-color: var(--background);
            }
        </style>

        <link rel="icon" href="/favicon.ico" sizes="any">
        <link rel="icon" href="/favicon.svg" type="image/svg+xml">
        <link rel="apple-touch-icon" href="/apple-touch-icon.png">

        @fonts

        @vite(['resources/css/app.css', 'resources/js/app.js', "resources/js/pages/{$page['component']}.vue"])
        <x-inertia::head>
            <title>{{ config('app.name', 'Laravel') }}</title>
        </x-inertia::head>
    </head>
    <body class="font-sans antialiased">
        <x-inertia::app />
    </body>
</html>
