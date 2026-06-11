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
    <body class="min-h-screen bg-white text-zinc-900 antialiased dark:bg-zinc-950 dark:text-zinc-100">
        <header class="fixed inset-x-0 top-0 z-40 border-b border-zinc-200 bg-white/80 backdrop-blur-md dark:border-white/5 dark:bg-zinc-950/70">
            <nav class="mx-auto flex h-16 max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8">
                <a href="{{ route('home') }}" wire:navigate class="flex items-center gap-2">
                    <span class="flex size-8 items-center justify-center rounded-full bg-orange-500 font-black text-zinc-950">
                        {{ str(config('app.name'))->substr(0, 1)->upper() }}
                    </span>
                    <span class="text-sm font-bold tracking-widest text-zinc-900 uppercase dark:text-white">{{ config('app.name') }}</span>
                </a>

                <div class="flex items-center gap-1 text-sm font-medium sm:gap-2">
                    @foreach ([
                        ['route' => 'home', 'label' => 'Início'],
                        ['route' => 'sessions.index', 'label' => 'Sessões'],
                        ['route' => 'games.index', 'label' => 'Jogos'],
                        ['route' => 'contact', 'label' => 'Contactos'],
                    ] as $item)
                        <a
                            href="{{ route($item['route']) }}"
                            wire:navigate
                            @class([
                                'rounded-full px-3 py-1.5 transition-colors sm:px-4',
                                'bg-zinc-100 text-zinc-900 dark:bg-white/10 dark:text-white' => request()->routeIs($item['route']),
                                'text-zinc-600 hover:bg-zinc-100 hover:text-zinc-900 dark:text-zinc-400 dark:hover:bg-white/5 dark:hover:text-white' => ! request()->routeIs($item['route']),
                            ])
                        >
                            {{ $item['label'] }}
                        </a>
                    @endforeach

                    <x-site.theme-toggle />

                    @auth
                        <a
                            href="{{ route('dashboard') }}"
                            class="ml-1 rounded-full bg-orange-500 px-4 py-1.5 font-semibold text-zinc-950 transition-colors hover:bg-orange-400"
                        >
                            Dashboard
                        </a>
                    @endauth
                </div>
            </nav>
        </header>

        <main>
            {{ $slot }}
        </main>

        @unless (request()->routeIs('home'))
            <x-site.sponsors variant="compact" />
        @endunless

        <footer class="border-t border-zinc-200 bg-zinc-50 dark:border-white/5 dark:bg-zinc-950">
            <div class="mx-auto flex max-w-7xl flex-col items-center justify-between gap-4 px-4 py-10 sm:flex-row sm:px-6 lg:px-8">
                <p class="text-sm text-zinc-500">
                    &copy; {{ now()->year }} {{ config('app.name') }} &mdash; Fotografia de desporto e espetáculos
                </p>

                <div class="flex items-center gap-6 text-sm text-zinc-500">
                    <a href="{{ route('sessions.index') }}" wire:navigate class="hover:text-zinc-900 dark:hover:text-white">Sessões</a>
                    <a href="{{ route('games.index') }}" wire:navigate class="hover:text-zinc-900 dark:hover:text-white">Jogos</a>
                    <a href="{{ route('contact') }}" wire:navigate class="hover:text-zinc-900 dark:hover:text-white">Contactos</a>
                    @guest
                        <a href="{{ route('login') }}" class="hover:text-zinc-900 dark:hover:text-white">Área reservada</a>
                    @endguest
                </div>
            </div>
        </footer>

        @fluxScripts
    </body>
</html>
