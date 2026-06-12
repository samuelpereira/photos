<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />

        <title>
            {{ filled($title ?? null) ? $title.' - '.config('app.name') : config('app.name') }}
        </title>

        <link rel="icon" href="/favicon.ico" sizes="any">
        <link rel="icon" href="/favicon.svg" type="image/svg+xml">
        <link rel="apple-touch-icon" href="/apple-touch-icon.png">

        @fonts

        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @fluxAppearance
    </head>
    <body class="min-h-screen overflow-x-hidden bg-white text-zinc-900 antialiased dark:bg-zinc-950 dark:text-zinc-100">
        <x-site.navbar />

        <main>
            {{ $slot }}
        </main>

        @unless (request()->routeIs('home'))
            <x-site.sponsors variant="compact" />
        @endunless

        <footer class="border-t border-zinc-200 bg-zinc-50 dark:border-white/5 dark:bg-zinc-950">
            <div class="mx-auto flex max-w-7xl flex-col items-center justify-between gap-4 px-4 py-8 text-center sm:flex-row sm:px-6 sm:py-10 sm:text-left lg:px-8">
                <p class="text-sm text-zinc-500">
                    &copy; {{ now()->year }} {{ config('app.name') }} &mdash; Fotografia de desporto e espetáculos
                </p>

                <div class="flex flex-wrap items-center justify-center gap-x-6 gap-y-2 text-sm text-zinc-500 sm:justify-end">
                    <a href="{{ route('games.index') }}" wire:navigate class="hover:text-zinc-900 dark:hover:text-white">Jogos</a>
                    <a href="{{ route('sessions.index') }}" wire:navigate class="hover:text-zinc-900 dark:hover:text-white">Sessões</a>
                    <a href="{{ route('contact') }}" wire:navigate class="hover:text-zinc-900 dark:hover:text-white">Contactos</a>
                </div>
            </div>
        </footer>

        @fluxScripts
    </body>
</html>
