@props(['game'])

<div class="flex flex-col gap-3 rounded-2xl border border-zinc-200 bg-white p-4 sm:flex-row sm:items-center sm:gap-6 sm:px-6 dark:border-white/5 dark:bg-zinc-900">
    <div class="flex shrink-0 flex-col text-xs text-zinc-500 sm:w-32">
        <span class="font-semibold text-zinc-700 dark:text-zinc-300">{{ $game->tip_off_at->translatedFormat('j M Y') }}</span>
        <span>{{ $game->competition ?? 'Amigável' }}</span>
        @if ($game->category)
            <span>{{ $game->category }}</span>
        @endif
    </div>

    <div class="flex flex-1 items-center justify-between gap-4">
        <span @class([
            'flex-1 truncate text-right font-bold uppercase tracking-tight',
            'text-zinc-900 dark:text-white' => $game->hasResult() && $game->home_score >= $game->away_score,
            'text-zinc-400 dark:text-zinc-500' => $game->hasResult() && $game->home_score < $game->away_score,
        ])>
            {{ $game->home_team }}
        </span>

        <span class="shrink-0 rounded-lg bg-zinc-100 px-3 py-1.5 font-mono text-lg font-black tracking-widest text-orange-500 dark:bg-zinc-950 dark:text-orange-400">
            @if ($game->hasResult())
                {{ $game->home_score }}&ndash;{{ $game->away_score }}
            @else
                &mdash;
            @endif
        </span>

        <span @class([
            'flex-1 truncate font-bold uppercase tracking-tight',
            'text-zinc-900 dark:text-white' => $game->hasResult() && $game->away_score >= $game->home_score,
            'text-zinc-400 dark:text-zinc-500' => $game->hasResult() && $game->away_score < $game->home_score,
        ])>
            {{ $game->away_team }}
        </span>
    </div>

    <div class="shrink-0 sm:w-28 sm:text-right">
        @if ($game->session && $game->session->is_published)
            <a
                href="{{ route('sessions.show', $game->session) }}"
                wire:navigate
                class="inline-flex items-center gap-1 text-sm font-semibold text-orange-500 hover:text-orange-600 dark:text-orange-400 dark:hover:text-orange-300"
            >
                Ver fotos
                <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                </svg>
            </a>
        @endif
    </div>
</div>
