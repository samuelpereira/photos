<div>
    {{-- Hero / banner --}}
    <section
        class="relative flex min-h-svh items-end overflow-hidden"
        @if ($heroPhotos->count() > 1)
            x-data="{
                current: 0,
                total: {{ $heroPhotos->count() }},
                timer: null,
                init() {
                    this.timer = setInterval(() => this.next(), 6000);
                },
                next() {
                    this.current = (this.current + 1) % this.total;
                },
                goTo(index) {
                    this.current = index;
                },
                destroy() {
                    clearInterval(this.timer);
                },
            }"
        @endif
    >
        @if ($heroPhotos->isNotEmpty())
            @foreach ($heroPhotos as $index => $heroPhoto)
                <img
                    src="{{ $heroPhoto->thumbnailUrl() }}"
                    alt="{{ $heroPhoto->caption ?? config('app.name') }}"
                    @class([
                        'absolute inset-0 size-full object-cover transition-opacity duration-1000 ease-in-out',
                        'opacity-100' => $heroPhotos->count() === 1,
                    ])
                    @if ($heroPhotos->count() > 1)
                        :class="current === {{ $index }} ? 'opacity-100' : 'opacity-0'"
                    @endif
                    @if ($index === 0)
                        fetchpriority="high"
                    @else
                        loading="lazy"
                    @endif
                />
            @endforeach
        @else
            <div class="absolute inset-0 bg-gradient-to-br from-zinc-900 via-zinc-950 to-black"></div>
        @endif

        @if ($heroPhotos->count() > 1)
            <div class="pointer-events-none absolute inset-x-0 bottom-6 z-20 flex justify-center gap-2 sm:bottom-8">
                @foreach ($heroPhotos as $index => $heroPhoto)
                    <button
                        type="button"
                        class="pointer-events-auto size-2 rounded-full transition-colors"
                        :class="current === {{ $index }} ? 'bg-white' : 'bg-white/40 hover:bg-white/70'"
                        x-on:click="goTo({{ $index }})"
                        aria-label="Banner {{ $index + 1 }}"
                    ></button>
                @endforeach
            </div>
        @endif

        <div class="pointer-events-none absolute inset-0 bg-gradient-to-t from-zinc-950 via-zinc-950/40 to-zinc-950/30"></div>

        <div class="relative mx-auto w-full max-w-7xl px-4 pt-24 pb-16 text-white sm:px-6 sm:pt-32 sm:pb-20 lg:px-8">
            <p class="mb-3 text-xs font-bold tracking-[0.25em] text-orange-400 uppercase sm:text-sm sm:tracking-[0.3em]">Fotografia desportiva &amp; espetáculos</p>
            <h1 class="max-w-3xl text-3xl leading-none font-black tracking-tight uppercase sm:text-6xl lg:text-7xl">
                Cada jogada,<br />
                <span class="text-orange-500">um momento</span> único
            </h1>
            <p class="mt-4 max-w-xl text-base text-zinc-300 sm:mt-6 sm:text-lg">
                Cobertura fotográfica de jogos de basquetebol e espetáculos de dança. Explora as galerias e revive cada momento.
            </p>

            <div class="mt-6 flex flex-col gap-3 sm:mt-8 sm:flex-row sm:flex-wrap sm:gap-4">
                <a
                    href="{{ route('games.index') }}"
                    wire:navigate
                    class="rounded-full bg-orange-500 px-7 py-3 text-center font-bold text-zinc-950 uppercase transition-colors hover:bg-orange-400"
                >
                    Jogos
                </a>
                <a
                    href="{{ route('sessions.index') }}"
                    wire:navigate
                    class="rounded-full border border-white/20 px-7 py-3 text-center font-bold uppercase backdrop-blur-sm transition-colors hover:bg-white/10"
                >
                    Sessões
                </a>
            </div>
        </div>
    </section>

    {{-- Jogos da semana --}}
    @if ($weekGames->isNotEmpty())
        <section class="mx-auto max-w-7xl px-4 py-14 sm:px-6 sm:py-20 lg:px-8">
            <div class="mb-8 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
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
        <div class="mx-auto max-w-7xl px-4 py-14 sm:px-6 sm:py-20 lg:px-8">
            <div class="mb-8 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
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
        <section class="mx-auto max-w-7xl px-4 py-14 sm:px-6 sm:py-20 lg:px-8">
            <div class="mb-8 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
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
