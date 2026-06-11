<div>
    {{-- Hero / banner --}}
    <section class="relative flex min-h-svh items-end overflow-hidden">
        @if ($heroPhoto)
            <img
                src="{{ $heroPhoto->thumbnailUrl() }}"
                alt="{{ $heroPhoto->caption ?? config('app.name') }}"
                class="absolute inset-0 size-full object-cover"
            />
        @else
            <div class="absolute inset-0 bg-gradient-to-br from-zinc-900 via-zinc-950 to-black"></div>
        @endif

        <div class="absolute inset-0 bg-gradient-to-t from-zinc-950 via-zinc-950/40 to-zinc-950/30"></div>

        <div class="relative mx-auto w-full max-w-7xl px-4 pt-32 pb-20 text-white sm:px-6 lg:px-8">
            <p class="mb-3 text-sm font-bold tracking-[0.3em] text-orange-400 uppercase">Fotografia desportiva &amp; espetáculos</p>
            <h1 class="max-w-3xl text-4xl leading-none font-black tracking-tight uppercase sm:text-6xl lg:text-7xl">
                Cada jogada,<br />
                <span class="text-orange-500">um momento</span> único
            </h1>
            <p class="mt-6 max-w-xl text-lg text-zinc-300">
                Cobertura fotográfica de jogos de basquetebol e espetáculos de dança. Explora as galerias e revive cada momento.
            </p>

            <div class="mt-8 flex flex-wrap gap-4">
                <a
                    href="{{ route('games.index') }}"
                    wire:navigate
                    class="rounded-full bg-orange-500 px-7 py-3 font-bold text-zinc-950 uppercase transition-colors hover:bg-orange-400"
                >
                    Jogos
                </a>
                <a
                    href="{{ route('sessions.index') }}"
                    wire:navigate
                    class="rounded-full border border-white/20 px-7 py-3 font-bold uppercase backdrop-blur-sm transition-colors hover:bg-white/10"
                >
                    Sessões
                </a>
            </div>
        </div>
    </section>

    {{-- Jogos da semana --}}
    @if ($weekGames->isNotEmpty())
        <section class="mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8">
            <div class="mb-8 flex items-end justify-between">
                <div>
                    <p class="text-sm font-bold tracking-widest text-orange-400 uppercase">Agenda</p>
                    <h2 class="text-3xl font-black tracking-tight uppercase sm:text-4xl">Jogos da semana</h2>
                </div>
                <a href="{{ route('games.index') }}" wire:navigate class="text-sm font-semibold text-zinc-500 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-white">
                    Ver todos &rarr;
                </a>
            </div>

            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($weekGames as $game)
                    <x-site.game-card :game="$game" />
                @endforeach
            </div>
        </section>
    @endif

    <x-site.sponsors variant="featured" />

    {{-- Eventos recentes --}}
    <section class="border-y border-zinc-200 bg-zinc-50 dark:border-white/5 dark:bg-zinc-900/40">
        <div class="mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8">
            <div class="mb-8 flex items-end justify-between">
                <div>
                    <p class="text-sm font-bold tracking-widest text-orange-400 uppercase">Portefólio</p>
                    <h2 class="text-3xl font-black tracking-tight uppercase sm:text-4xl">Sessões recentes</h2>
                </div>
                <a href="{{ route('sessions.index') }}" wire:navigate class="text-sm font-semibold text-zinc-500 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-white">
                    Ver todas &rarr;
                </a>
            </div>

            @if ($recentSessions->isEmpty())
                <p class="text-zinc-500">Ainda não há sessões publicadas.</p>
            @else
                <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($recentSessions as $session)
                        <x-site.session-card :session="$session" />
                    @endforeach
                </div>
            @endif
        </div>
    </section>

    {{-- Últimos resultados --}}
    @if ($recentResults->isNotEmpty())
        <section class="mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8">
            <div class="mb-8 flex items-end justify-between">
                <div>
                    <p class="text-sm font-bold tracking-widest text-orange-400 uppercase">Arquivo</p>
                    <h2 class="text-3xl font-black tracking-tight uppercase sm:text-4xl">Últimos resultados</h2>
                </div>
                <a href="{{ route('games.index') }}" wire:navigate class="text-sm font-semibold text-zinc-500 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-white">
                    Ver arquivo &rarr;
                </a>
            </div>

            <div class="space-y-3">
                @foreach ($recentResults as $game)
                    <x-site.score-row :game="$game" />
                @endforeach
            </div>
        </section>
    @endif
</div>
