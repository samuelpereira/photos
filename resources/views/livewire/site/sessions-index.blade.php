<div class="mx-auto max-w-7xl px-4 pt-28 pb-20 sm:px-6 lg:px-8">
    <p class="text-sm font-bold tracking-widest text-orange-400 uppercase">Portefólio</p>
    <h1 class="text-4xl font-black tracking-tight uppercase sm:text-5xl">Sessões</h1>

    <div class="mt-8 flex flex-wrap gap-2">
        <button
            type="button"
            wire:click="setType('')"
            @class([
                'rounded-full px-4 py-2 text-sm font-semibold transition-colors',
                'bg-orange-500 text-zinc-950' => $type === '',
                'bg-zinc-100 text-zinc-600 hover:bg-zinc-200 hover:text-zinc-900 dark:bg-zinc-900 dark:text-zinc-400 dark:hover:bg-zinc-800 dark:hover:text-white' => $type !== '',
            ])
        >
            Todos
        </button>

        @foreach ($sessionTypes as $sessionType)
            <button
                type="button"
                wire:click="setType('{{ $sessionType->slug }}')"
                @class([
                    'rounded-full px-4 py-2 text-sm font-semibold transition-colors',
                    'bg-orange-500 text-zinc-950' => $type === $sessionType->slug,
                    'bg-zinc-100 text-zinc-600 hover:bg-zinc-200 hover:text-zinc-900 dark:bg-zinc-900 dark:text-zinc-400 dark:hover:bg-zinc-800 dark:hover:text-white' => $type !== $sessionType->slug,
                ])
            >
                {{ $sessionType->name }}
            </button>
        @endforeach
    </div>

    @if ($sessions->isEmpty())
        <p class="mt-12 text-zinc-500">Não há sessões para mostrar.</p>
    @else
        <div class="mt-10 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($sessions as $session)
                <x-site.session-card :session="$session" :key="$session->id" />
            @endforeach
        </div>

        <div class="mt-10">
            {{ $sessions->links() }}
        </div>
    @endif
</div>
