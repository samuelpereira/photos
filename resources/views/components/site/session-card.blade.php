@props(['session'])

<a
    href="{{ route('sessions.show', $session) }}"
    wire:navigate
    class="group relative block aspect-[4/3] overflow-hidden rounded-2xl bg-zinc-900"
>
    @if ($session->coverUrl())
        <img
            src="{{ $session->coverUrl() }}"
            alt="{{ $session->name }}"
            loading="lazy"
            class="absolute inset-0 size-full object-cover transition duration-500 group-hover:scale-105"
        />
    @endif

    <div class="absolute inset-0 bg-gradient-to-t from-zinc-950/90 via-zinc-950/20 to-transparent"></div>

    <div class="absolute inset-x-0 bottom-0 p-5">
        @if ($session->sessionType)
            <span @class([
                'mb-2 inline-block rounded-full px-2.5 py-1 text-xs font-semibold uppercase tracking-wider',
                'bg-orange-500/90 text-zinc-950' => $session->sessionType->color === 'orange',
                'bg-fuchsia-500/90 text-white' => $session->sessionType->color === 'fuchsia',
                'bg-blue-500/90 text-white' => $session->sessionType->color === 'blue',
                'bg-green-500/90 text-zinc-950' => $session->sessionType->color === 'green',
                'bg-zinc-700/90 text-white' => ! in_array($session->sessionType->color, ['orange', 'fuchsia', 'blue', 'green']),
            ])>
                {{ $session->sessionType->name }}
            </span>
        @endif

        <h3 class="text-lg leading-tight font-bold text-white">{{ $session->name }}</h3>
        <p class="mt-1 text-sm text-zinc-400">
            {{ $session->session_date->translatedFormat('j \d\e F \d\e Y') }} · {{ $session->photos_count ?? $session->photos->count() }} fotos
        </p>
    </div>
</a>
