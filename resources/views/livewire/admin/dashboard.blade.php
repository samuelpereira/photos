<div class="space-y-6">
    <div>
        <flux:heading size="xl" level="1">Dashboard</flux:heading>
        <flux:subheading>Visão geral do site</flux:subheading>
    </div>

    <div class="grid gap-4 md:grid-cols-3">
        <a href="{{ route('admin.sessions.index') }}" wire:navigate class="rounded-xl border border-neutral-200 p-6 transition-colors hover:border-neutral-300 dark:border-neutral-700 dark:hover:border-neutral-600">
            <flux:text>Sessões</flux:text>
            <flux:heading size="xl" class="mt-1">{{ $sessionCount }}</flux:heading>
        </a>
        <a href="{{ route('admin.sessions.index') }}" wire:navigate class="rounded-xl border border-neutral-200 p-6 transition-colors hover:border-neutral-300 dark:border-neutral-700 dark:hover:border-neutral-600">
            <flux:text>Fotos</flux:text>
            <flux:heading size="xl" class="mt-1">{{ $photoCount }}</flux:heading>
        </a>
        <a href="{{ route('admin.games.index') }}" wire:navigate class="rounded-xl border border-neutral-200 p-6 transition-colors hover:border-neutral-300 dark:border-neutral-700 dark:hover:border-neutral-600">
            <flux:text>Jogos</flux:text>
            <flux:heading size="xl" class="mt-1">{{ $gameCount }}</flux:heading>
        </a>
    </div>

    <div class="grid gap-4 lg:grid-cols-2">
        <div class="rounded-xl border border-neutral-200 p-6 dark:border-neutral-700">
            <flux:heading size="lg">Próximos jogos</flux:heading>

            <div class="mt-4 space-y-3">
                @forelse ($upcomingGames as $game)
                    <div class="flex items-center justify-between gap-4 text-sm">
                        <span class="font-medium">{{ $game->home_team }} vs {{ $game->away_team }}</span>
                        <span class="shrink-0 text-zinc-500">{{ $game->tip_off_at->format('d/m H:i') }}</span>
                    </div>
                @empty
                    <flux:text>Não há jogos agendados.</flux:text>
                @endforelse
            </div>
        </div>

        <div class="rounded-xl border border-neutral-200 p-6 dark:border-neutral-700">
            <flux:heading size="lg">Jogos sem resultado</flux:heading>

            <div class="mt-4 space-y-3">
                @forelse ($gamesWithoutResult as $game)
                    <div class="flex items-center justify-between gap-4 text-sm">
                        <span class="font-medium">{{ $game->home_team }} vs {{ $game->away_team }}</span>
                        <span class="shrink-0 text-zinc-500">{{ $game->tip_off_at->format('d/m/Y') }}</span>
                    </div>
                @empty
                    <flux:text>Tudo em dia.</flux:text>
                @endforelse
            </div>

            @if ($gamesWithoutResult->isNotEmpty())
                <flux:button size="sm" class="mt-4" :href="route('admin.games.index')" wire:navigate>
                    Inserir resultados
                </flux:button>
            @endif
        </div>
    </div>
</div>
