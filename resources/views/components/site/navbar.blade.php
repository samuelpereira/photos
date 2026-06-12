@php
    $navItems = [
        ['route' => 'home', 'label' => 'Início'],
        ['route' => 'games.index', 'label' => 'Jogos'],
        ['route' => 'sessions.index', 'label' => 'Sessões'],
        ['route' => 'contact', 'label' => 'Contactos'],
    ];
@endphp

<header
    x-data="{ open: false }"
    x-on:click.outside="open = false"
    x-on:keydown.escape.window="open = false"
    x-effect="document.body.style.overflow = open ? 'hidden' : ''"
    class="fixed inset-x-0 top-0 z-40 border-b border-zinc-200 bg-white/80 backdrop-blur-md dark:border-white/5 dark:bg-zinc-950/70"
>
    <nav class="mx-auto flex h-16 max-w-7xl items-center justify-between gap-3 px-4 sm:px-6 lg:px-8">
        <a href="{{ route('home') }}" wire:navigate class="flex min-w-0 items-center gap-2">
            <span class="flex size-8 shrink-0 items-center justify-center rounded-full bg-orange-500 font-black text-zinc-950">
                {{ str(config('app.name'))->substr(0, 1)->upper() }}
            </span>
            <span class="truncate text-sm font-bold tracking-widest text-zinc-900 uppercase dark:text-white sm:max-w-none">
                {{ config('app.name') }}
            </span>
        </a>

        <div class="hidden items-center gap-1 text-sm font-medium lg:flex lg:gap-2">
            @foreach ($navItems as $item)
                <a
                    href="{{ route($item['route']) }}"
                    wire:navigate
                    @class([
                        'rounded-full px-3 py-1.5 transition-colors lg:px-4',
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

        <div class="flex items-center gap-1 lg:hidden">
            <x-site.theme-toggle />

            <button
                type="button"
                class="rounded-full p-2 text-zinc-600 transition-colors hover:bg-zinc-100 hover:text-zinc-900 dark:text-zinc-400 dark:hover:bg-white/5 dark:hover:text-white"
                x-on:click="open = ! open"
                :aria-expanded="open"
                aria-controls="site-mobile-menu"
                :aria-label="open ? 'Fechar menu' : 'Abrir menu'"
            >
                <svg x-show="! open" x-cloak class="size-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                </svg>
                <svg x-show="open" x-cloak class="size-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
    </nav>

    <div
        id="site-mobile-menu"
        x-cloak
        x-show="open"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 -translate-y-2"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 -translate-y-2"
        class="border-t border-zinc-200 bg-white/95 backdrop-blur-md lg:hidden dark:border-white/5 dark:bg-zinc-950/95"
    >
        <div class="mx-auto max-w-7xl space-y-1 px-4 py-4 sm:px-6">
            @foreach ($navItems as $item)
                <a
                    href="{{ route($item['route']) }}"
                    wire:navigate
                    x-on:click="open = false"
                    @class([
                        'block rounded-xl px-4 py-3 text-base font-semibold transition-colors',
                        'bg-zinc-100 text-zinc-900 dark:bg-white/10 dark:text-white' => request()->routeIs($item['route']),
                        'text-zinc-700 hover:bg-zinc-100 hover:text-zinc-900 dark:text-zinc-300 dark:hover:bg-white/5 dark:hover:text-white' => ! request()->routeIs($item['route']),
                    ])
                >
                    {{ $item['label'] }}
                </a>
            @endforeach

            @auth
                <a
                    href="{{ route('dashboard') }}"
                    x-on:click="open = false"
                    class="mt-2 block rounded-xl bg-orange-500 px-4 py-3 text-center text-base font-bold text-zinc-950 transition-colors hover:bg-orange-400"
                >
                    Dashboard
                </a>
            @endauth
        </div>
    </div>
</header>
