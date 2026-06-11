@props(['variant' => 'featured'])

@php
    $sponsors = \App\Models\Sponsor::query()
        ->active()
        ->whereNotNull('logo_path')
        ->orderBy('sort_order')
        ->get();
    $compact = $variant === 'compact';
@endphp

@if ($sponsors->isNotEmpty())
    <section @class([
        'border-t border-zinc-200 dark:border-white/5',
        'bg-zinc-50 py-10 dark:bg-zinc-900/50' => ! $compact,
        'bg-zinc-50 py-5 dark:bg-zinc-950' => $compact,
    ])>
        <div @class([
            'mx-auto max-w-7xl px-4 sm:px-6 lg:px-8',
            'flex flex-col items-center gap-4 sm:flex-row sm:justify-between' => $compact,
        ])>
            <p @class([
                'text-xs font-bold tracking-[0.25em] text-zinc-500 uppercase',
                'mb-6 text-center' => ! $compact,
                'shrink-0' => $compact,
            ])>
                Patrocinadores
            </p>

            <div @class([
                'flex flex-wrap items-center justify-center gap-x-10 gap-y-6' => ! $compact,
                'flex flex-wrap items-center justify-center gap-x-6 gap-y-3 sm:justify-end' => $compact,
            ])>
                @foreach ($sponsors as $sponsor)
                    @if ($sponsor->url)
                        <a
                            href="{{ $sponsor->url }}"
                            target="_blank"
                            rel="noopener noreferrer sponsored"
                            title="{{ $sponsor->name }}"
                            class="opacity-80 transition-opacity hover:opacity-100"
                        >
                            <img
                                src="{{ $sponsor->logoUrl() }}"
                                alt="{{ $sponsor->name }}"
                                loading="lazy"
                                @class([
                                    'object-contain',
                                    'h-10 max-w-[9rem] sm:h-12 sm:max-w-[11rem]' => ! $compact,
                                    'h-7 max-w-[6rem] sm:h-8 sm:max-w-[7rem]' => $compact,
                                ])
                            />
                        </a>
                    @else
                        <img
                            src="{{ $sponsor->logoUrl() }}"
                            alt="{{ $sponsor->name }}"
                            loading="lazy"
                            @class([
                                'object-contain opacity-80',
                                'h-10 max-w-[9rem] sm:h-12 sm:max-w-[11rem]' => ! $compact,
                                'h-7 max-w-[6rem] sm:h-8 sm:max-w-[7rem]' => $compact,
                            ])
                        />
                    @endif
                @endforeach
            </div>
        </div>
    </section>
@endif
