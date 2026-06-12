<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <flux:button variant="ghost" size="sm" icon="arrow-left" :href="route('admin.sessions.index')" wire:navigate>
                Sessões
            </flux:button>
            <flux:heading size="xl" level="1" class="mt-2">{{ $session->name }}</flux:heading>
            <flux:subheading>{{ $session->photos->count() }} fotos · {{ $session->session_date->format('d/m/Y') }}</flux:subheading>
        </div>

        <flux:button :href="route('sessions.show', $session)" icon="eye" target="_blank">Ver no site</flux:button>
    </div>

    <div
        x-data="{ dragging: false }"
        x-on:dragenter.prevent="dragging = true"
        x-on:dragover.prevent="dragging = true"
        x-on:dragleave.prevent="if (! $event.currentTarget.contains($event.relatedTarget)) dragging = false"
        x-on:drop.prevent="
            dragging = false;
            const input = $refs.uploadInput;
            const dt = new DataTransfer();

            [...$event.dataTransfer.files].forEach((file) => {
                if (file.type.startsWith('image/')) {
                    dt.items.add(file);
                }
            });

            if (dt.files.length === 0) {
                return;
            }

            input.files = dt.files;
            input.dispatchEvent(new Event('change', { bubbles: true }));
        "
    >
        <label
            :class="dragging ? 'border-orange-500 bg-orange-500/5 dark:bg-orange-500/10' : 'border-zinc-300 dark:border-zinc-600'"
            class="flex cursor-pointer flex-col items-center justify-center gap-2 rounded-xl border-2 border-dashed p-10 text-center transition-colors hover:border-zinc-400 dark:hover:border-zinc-500"
        >
            <flux:icon.arrow-up-tray class="size-8 text-zinc-400" />
            <flux:text>Clica ou arrasta fotos para aqui para as carregar</flux:text>
            <flux:text class="text-xs">JPG, PNG ou WebP · máx. {{ intval(config('photos.max_upload_kb', 25600) / 1024) }} MB por foto</flux:text>
            <input x-ref="uploadInput" type="file" wire:model="uploads" multiple accept="image/*" class="hidden" />

            <div wire:loading wire:target="uploads" class="text-sm font-medium text-orange-500">
                A enviar e processar fotos&hellip; Isto pode demorar com ficheiros grandes.
            </div>
        </label>
    </div>

    @error('uploads')
        <flux:callout variant="danger" icon="exclamation-triangle" heading="{{ $message }}" />
    @enderror

    @error('uploads.*')
        <flux:callout variant="danger" icon="exclamation-triangle" heading="{{ $message }}" />
    @enderror

    @if ($session->photos->isEmpty())
        <flux:text>Esta sessão ainda não tem fotos.</flux:text>
    @else
        <flux:text class="text-sm text-zinc-500">Arrasta uma foto para reordenar (ícone <flux:icon.bars-3 class="inline size-4 align-text-bottom" /> ou qualquer parte da imagem).</flux:text>

        <div
            class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4"
            x-data="{ draggedId: null }"
        >
            @foreach ($session->photos as $photo)
                <div
                    class="group relative overflow-hidden rounded-xl border border-zinc-200 bg-zinc-100 transition-shadow dark:border-zinc-700 dark:bg-zinc-900"
                    wire:key="photo-{{ $photo->id }}"
                    data-photo-id="{{ $photo->id }}"
                    draggable="true"
                    x-bind:class="draggedId === {{ $photo->id }} ? 'opacity-50 ring-2 ring-orange-500' : ''"
                    x-on:dragstart="
                        draggedId = {{ $photo->id }};
                        $event.dataTransfer.effectAllowed = 'move';
                        $event.dataTransfer.setData('text/plain', '{{ $photo->id }}');
                    "
                    x-on:dragend="draggedId = null"
                    x-on:dragover.prevent="$event.dataTransfer.dropEffect = 'move'"
                    x-on:drop.prevent="
                        if (! draggedId || draggedId === {{ $photo->id }}) return;
                        const ids = [...$el.parentElement.querySelectorAll('[data-photo-id]')].map(el => Number(el.dataset.photoId));
                        const from = ids.indexOf(draggedId);
                        const to = ids.indexOf({{ $photo->id }});
                        if (from === -1 || to === -1) return;
                        ids.splice(from, 1);
                        ids.splice(to, 0, draggedId);
                        $wire.reorderPhotos(ids);
                        draggedId = null;
                    "
                >
                    <img src="{{ $photo->originalUrl() }}" alt="" loading="lazy" class="aspect-[4/3] w-full object-cover" draggable="false" />

                    <div class="absolute inset-x-0 top-0 flex items-start justify-between gap-1 p-2">
                        <div class="flex items-start gap-1">
                            <div
                                title="Arrastar para reordenar"
                                class="cursor-grab rounded-md bg-black/50 p-1 text-white opacity-70 transition-opacity active:cursor-grabbing group-hover:opacity-100"
                            >
                                <flux:icon.bars-3 class="size-4" />
                            </div>

                            <div class="flex gap-1">
                            @if ($photo->is_cover)
                                <flux:badge size="sm" color="orange">Capa</flux:badge>
                            @endif
                            @if ($photo->is_featured)
                                <flux:badge size="sm" color="yellow">Banner</flux:badge>
                            @endif
                            </div>
                        </div>

                        <div class="flex gap-1 opacity-0 transition-opacity group-hover:opacity-100" x-on:dragstart.stop>
                            <flux:button
                                size="xs"
                                icon="star"
                                tooltip="{{ $photo->is_featured ? 'Remover do banner' : 'Usar no banner da homepage' }}"
                                wire:click="toggleFeatured({{ $photo->id }})"
                            />
                            <flux:button
                                size="xs"
                                icon="photo"
                                tooltip="Definir como capa"
                                wire:click="setCover({{ $photo->id }})"
                            />
                            <flux:button
                                size="xs"
                                variant="danger"
                                icon="trash"
                                tooltip="Eliminar"
                                wire:click="deletePhoto({{ $photo->id }})"
                                wire:confirm="Eliminar esta foto?"
                            />
                        </div>
                    </div>

                    <div class="p-2" x-on:dragstart.stop>
                        <flux:input
                            size="sm"
                            wire:model.blur="captions.{{ $photo->id }}"
                            placeholder="Legenda&hellip;"
                        />
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
