@props(['game'])

<div class="flex flex-col gap-4 rounded-2xl border border-zinc-200 bg-white p-5 transition-colors hover:border-orange-500/40 dark:border-white/5 dark:bg-zinc-900">
    <div class="flex items-center justify-between gap-2 text-xs font-semibold tracking-wider text-zinc-500 uppercase">
        <span class="rounded-full bg-orange-500/10 px-2.5 py-1 text-orange-500 dark:text-orange-400">
            {{ $game->competition ?? 'Amigável' }}
        </span>
        @if ($game->category)
            <span>{{ $game->category }}</span>
        @endif
    </div>

    <div class="space-y-2">
        <p class="text-lg leading-tight font-extrabold tracking-tight text-zinc-900 uppercase dark:text-white">{{ $game->home_team }}</p>
        <p class="text-xs font-bold text-zinc-400 uppercase dark:text-zinc-500">vs</p>
        <p class="text-lg leading-tight font-extrabold tracking-tight text-zinc-900 uppercase dark:text-white">{{ $game->away_team }}</p>
    </div>

    <div class="mt-auto space-y-1 border-t border-zinc-200 pt-4 text-sm text-zinc-500 dark:border-white/5 dark:text-zinc-400">
        <p class="font-semibold text-zinc-900 dark:text-white">{{ $game->tip_off_at->translatedFormat('D, j M · H:i') }}</p>
        @if ($game->venue)
            <p>{{ $game->venue }}</p>
        @endif
    </div>
</div>
