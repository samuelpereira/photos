<div class="mx-auto max-w-7xl px-4 pt-24 pb-16 sm:px-6 sm:pt-28 sm:pb-20 lg:px-8">
    <p class="text-sm font-bold tracking-widest text-orange-400 uppercase">Fala comigo</p>
    <h1 class="text-4xl font-black tracking-tight uppercase sm:text-5xl">Contactos</h1>

    <p class="mt-4 max-w-2xl text-lg text-zinc-600 dark:text-zinc-400">
        {{ $contact['tagline'] }}. Para pedidos de cobertura fotográfica, parcerias ou informações sobre sessões, usa os contactos abaixo.
    </p>

    <div class="mt-12 grid gap-6 lg:grid-cols-2">
        <section class="rounded-2xl border border-zinc-200 bg-white p-6 dark:border-white/5 dark:bg-zinc-900 sm:p-8">
            <h2 class="text-xl font-black tracking-tight uppercase">Informações</h2>

            <dl class="mt-6 space-y-6">
                @if (filled($contact['name']))
                    <div>
                        <dt class="text-xs font-bold tracking-widest text-zinc-500 uppercase">Nome</dt>
                        <dd class="mt-1 text-lg font-semibold text-zinc-900 dark:text-white">{{ $contact['name'] }}</dd>
                    </div>
                @endif

                @if (filled($contact['email']))
                    <div>
                        <dt class="text-xs font-bold tracking-widest text-zinc-500 uppercase">Email</dt>
                        <dd class="mt-1">
                            <a href="mailto:{{ $contact['email'] }}" class="text-lg font-semibold text-orange-500 transition-colors hover:text-orange-600 dark:text-orange-400 dark:hover:text-orange-300">
                                {{ $contact['email'] }}
                            </a>
                        </dd>
                    </div>
                @endif

                @if (filled($contact['phone']))
                    <div>
                        <dt class="text-xs font-bold tracking-widest text-zinc-500 uppercase">Telefone</dt>
                        <dd class="mt-1">
                            <a href="tel:{{ preg_replace('/\s+/', '', $contact['phone']) }}" class="text-lg font-semibold text-zinc-900 transition-colors hover:text-orange-500 dark:text-white dark:hover:text-orange-400">
                                {{ $contact['phone'] }}
                            </a>
                        </dd>
                    </div>
                @endif

                @if (filled($contact['location']))
                    <div>
                        <dt class="text-xs font-bold tracking-widest text-zinc-500 uppercase">Localização</dt>
                        <dd class="mt-1 text-lg text-zinc-600 dark:text-zinc-300">{{ $contact['location'] }}</dd>
                    </div>
                @endif
            </dl>
        </section>

        <section class="flex flex-col justify-between rounded-2xl border border-orange-500/20 bg-gradient-to-br from-zinc-50 to-white p-6 dark:from-zinc-900 dark:to-zinc-950 sm:p-8">
            <div>
                <h2 class="text-xl font-black tracking-tight uppercase">Redes sociais</h2>
                <p class="mt-3 text-zinc-600 dark:text-zinc-400">
                    Segue o trabalho mais recente e entra em contacto por mensagem direta.
                </p>

                <div class="mt-6 flex flex-wrap gap-3">
                    @if (filled($contact['instagram']))
                        <a
                            href="{{ str_starts_with($contact['instagram'], 'http') ? $contact['instagram'] : 'https://instagram.com/'.ltrim($contact['instagram'], '@') }}"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="inline-flex items-center gap-2 rounded-full border border-zinc-200 bg-zinc-100 px-5 py-2.5 text-sm font-semibold transition-colors hover:border-orange-500/40 hover:bg-orange-500/10 hover:text-orange-600 dark:border-white/10 dark:bg-white/5 dark:hover:text-orange-300"
                        >
                            Instagram
                        </a>
                    @endif

                    @if (filled($contact['facebook']))
                        <a
                            href="{{ str_starts_with($contact['facebook'], 'http') ? $contact['facebook'] : 'https://facebook.com/'.$contact['facebook'] }}"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="inline-flex items-center gap-2 rounded-full border border-zinc-200 bg-zinc-100 px-5 py-2.5 text-sm font-semibold transition-colors hover:border-orange-500/40 hover:bg-orange-500/10 hover:text-orange-600 dark:border-white/10 dark:bg-white/5 dark:hover:text-orange-300"
                        >
                            Facebook
                        </a>
                    @endif
                </div>
            </div>

            @if (filled($contact['email']))
                <a
                    href="mailto:{{ $contact['email'] }}?subject={{ rawurlencode('Pedido de informação - '.config('app.name')) }}"
                    class="mt-8 inline-flex w-full items-center justify-center rounded-full bg-orange-500 px-7 py-3 font-bold text-zinc-950 uppercase transition-colors hover:bg-orange-400 sm:w-auto"
                >
                    Enviar email
                </a>
            @endif
        </section>
    </div>
</div>
