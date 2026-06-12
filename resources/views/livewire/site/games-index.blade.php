<div class="mx-auto max-w-7xl px-4 pt-24 pb-16 sm:px-6 sm:pt-28 sm:pb-20 lg:px-8">
    <p class="text-sm font-bold tracking-widest text-orange-400 uppercase">Calendário</p>
    <h1 class="text-4xl font-black tracking-tight uppercase sm:text-5xl">Jogos</h1>

    {{-- Próximos jogos --}}
    <section class="mt-12">
        <h2 class="mb-6 text-2xl font-black tracking-tight uppercase">Próximos jogos</h2>

        @if ($upcomingGames->isEmpty())
            <p class="text-zinc-500">Não há jogos agendados de momento.</p>
        @else
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($upcomingGames as $game)
                    <x-site.game-card :game="$game" />
                @endforeach
            </div>
        @endif
    </section>

    {{-- Jogos passados --}}
    <section class="mt-16">
        <h2 class="mb-6 text-2xl font-black tracking-tight uppercase">Jogos passados</h2>

        @if ($pastGames->isEmpty())
            <p class="text-zinc-500">Ainda não há jogos no arquivo.</p>
        @else
            <div class="space-y-3">
                @foreach ($pastGames as $game)
                    <x-site.score-row :game="$game" />
                @endforeach
            </div>

            <div class="mt-10">
                {{ $pastGames->links() }}
            </div>
        @endif
    </section>
</div>
