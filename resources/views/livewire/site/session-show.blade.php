<div
    x-data="{
        lightbox: null,
        photos: {{ Js::from($session->photos->map(fn ($photo) => ['url' => $photo->url(), 'caption' => $photo->caption])) }},
        open(index) { this.lightbox = index; document.body.style.overflow = 'hidden' },
        close() { this.lightbox = null; document.body.style.overflow = '' },
        next() { this.lightbox = (this.lightbox + 1) % this.photos.length },
        prev() { this.lightbox = (this.lightbox - 1 + this.photos.length) % this.photos.length },
    }"
    @keydown.window.escape="close()"
    @keydown.window.arrow-right="lightbox !== null && next()"
    @keydown.window.arrow-left="lightbox !== null && prev()"
>
    <section class="relative flex min-h-[55svh] items-end overflow-hidden">
        @if ($session->coverUrl())
            <img src="{{ $session->coverUrl() }}" alt="{{ $session->name }}" class="absolute inset-0 size-full object-cover" fetchpriority="high" />
        @endif
        <div class="absolute inset-0 bg-gradient-to-t from-zinc-950 via-zinc-950/50 to-zinc-950/30"></div>

        <div class="relative mx-auto w-full max-w-7xl px-4 pt-28 pb-12 text-white sm:px-6 lg:px-8">
            <a href="{{ route('sessions.index') }}" wire:navigate class="mb-4 inline-flex items-center gap-1 text-sm font-semibold text-zinc-300 hover:text-white">
                <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" />
                </svg>
                Sessões
            </a>

            @if ($session->sessionType)
                <span @class([
                    'mb-3 block w-fit rounded-full px-2.5 py-1 text-xs font-semibold uppercase tracking-wider',
                    'bg-orange-500/90 text-zinc-950' => $session->sessionType->color === 'orange',
                    'bg-fuchsia-500/90 text-white' => $session->sessionType->color === 'fuchsia',
                    'bg-blue-500/90 text-white' => $session->sessionType->color === 'blue',
                    'bg-green-500/90 text-zinc-950' => $session->sessionType->color === 'green',
                    'bg-zinc-700/90 text-white' => ! in_array($session->sessionType->color, ['orange', 'fuchsia', 'blue', 'green']),
                ])>
                    {{ $session->sessionType->name }}
                </span>
            @endif

            <h1 class="max-w-3xl text-3xl font-black tracking-tight uppercase sm:text-5xl">{{ $session->name }}</h1>
            <p class="mt-3 text-zinc-300">
                {{ $session->session_date->translatedFormat('j \d\e F \d\e Y') }} · {{ $session->photos->count() }} fotos
            </p>

            @if ($session->description)
                <p class="mt-4 max-w-2xl text-zinc-200">{{ $session->description }}</p>
            @endif
        </div>
    </section>

    @if ($session->games->isNotEmpty())
        <section class="mx-auto max-w-7xl px-4 pt-10 sm:px-6 lg:px-8">
            <div class="space-y-3">
                @foreach ($session->games as $game)
                    <x-site.score-row :game="$game->setRelation('session', null)" />
                @endforeach
            </div>
        </section>
    @endif

    <section class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
        @if ($session->photos->isEmpty())
            <p class="text-zinc-500">Esta galeria ainda não tem fotos.</p>
        @else
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($session->photos as $index => $photo)
                    <button
                        type="button"
                        class="group block w-full cursor-zoom-in overflow-hidden rounded-xl"
                        @click="open({{ $index }})"
                    >
                        <img
                            src="{{ $photo->thumbnailUrl() }}"
                            alt="{{ $photo->caption ?? $session->name }}"
                            loading="lazy"
                            class="w-full transition duration-300 group-hover:scale-[1.02] group-hover:brightness-110"
                        />
                    </button>
                @endforeach
            </div>
        @endif
    </section>

    <div
        x-cloak
        x-show="lightbox !== null"
        x-transition.opacity
        class="fixed inset-0 z-50 flex items-center justify-center bg-zinc-950/95 p-4"
        @click.self="close()"
    >
        <button type="button" class="absolute top-4 right-4 rounded-full bg-white/10 p-2 hover:bg-white/20" @click="close()">
            <svg class="size-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
            </svg>
        </button>

        <button type="button" class="absolute left-2 rounded-full bg-white/10 p-2 hover:bg-white/20 sm:left-6" @click="prev()">
            <svg class="size-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" />
            </svg>
        </button>

        <template x-if="lightbox !== null">
            <figure class="max-h-full">
                <img :src="photos[lightbox].url" :alt="photos[lightbox].caption ?? ''" class="max-h-[85svh] rounded-lg object-contain" />
                <figcaption
                    x-show="photos[lightbox].caption"
                    x-text="photos[lightbox].caption"
                    class="mt-3 text-center text-sm text-zinc-400"
                ></figcaption>
            </figure>
        </template>

        <button type="button" class="absolute right-2 rounded-full bg-white/10 p-2 hover:bg-white/20 sm:right-6" @click="next()">
            <svg class="size-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
            </svg>
        </button>
    </div>
</div>
