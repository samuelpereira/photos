<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <flux:heading size="xl" level="1">Patrocinadores</flux:heading>
            <flux:subheading>Logótipos e links exibidos no site público</flux:subheading>
        </div>

        <flux:button variant="primary" icon="plus" wire:click="create">Novo patrocinador</flux:button>
    </div>

    <flux:table :paginate="$sponsors">
        <flux:table.columns>
            <flux:table.column>Logo</flux:table.column>
            <flux:table.column>Nome</flux:table.column>
            <flux:table.column>Site</flux:table.column>
            <flux:table.column>Estado</flux:table.column>
            <flux:table.column></flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @foreach ($sponsors as $sponsor)
                <flux:table.row :key="$sponsor->id">
                    <flux:table.cell>
                        @if ($sponsor->logoUrl())
                            <img src="{{ $sponsor->logoUrl() }}" alt="{{ $sponsor->name }}" class="h-10 max-w-32 object-contain" />
                        @else
                            <span class="text-sm text-zinc-400">&mdash;</span>
                        @endif
                    </flux:table.cell>
                    <flux:table.cell variant="strong">{{ $sponsor->name }}</flux:table.cell>
                    <flux:table.cell>
                        @if ($sponsor->url)
                            <a href="{{ $sponsor->url }}" target="_blank" rel="noopener noreferrer" class="text-sm text-orange-500 hover:underline">
                                {{ parse_url($sponsor->url, PHP_URL_HOST) }}
                            </a>
                        @else
                            <span class="text-zinc-400">&mdash;</span>
                        @endif
                    </flux:table.cell>
                    <flux:table.cell>
                        <flux:badge size="sm" :color="$sponsor->is_active ? 'green' : 'zinc'">
                            {{ $sponsor->is_active ? 'Ativo' : 'Inativo' }}
                        </flux:badge>
                    </flux:table.cell>
                    <flux:table.cell align="end">
                        <div class="flex items-center justify-end gap-1">
                            <flux:button size="sm" variant="ghost" icon="pencil-square" wire:click="edit({{ $sponsor->id }})" />
                            <flux:button
                                size="sm"
                                variant="ghost"
                                icon="trash"
                                wire:click="delete({{ $sponsor->id }})"
                                wire:confirm="Eliminar este patrocinador?"
                            />
                        </div>
                    </flux:table.cell>
                </flux:table.row>
            @endforeach
        </flux:table.rows>
    </flux:table>

    <flux:modal name="sponsor-form" class="md:w-[28rem]">
        <form wire:submit="save" class="space-y-6">
            <flux:heading size="lg">{{ $editingId ? 'Editar patrocinador' : 'Novo patrocinador' }}</flux:heading>

            <flux:input wire:model="name" label="Nome" placeholder="Ex.: Pingo Doce" />

            <flux:input wire:model="url" type="url" label="Site" placeholder="https://www.exemplo.pt" />

            <flux:switch wire:model="is_active" label="Visível no site" />

            <div class="space-y-2">
                <flux:label>Logótipo</flux:label>

                @if ($currentLogoPath && ! $logo)
                    <img src="{{ \App\Support\PhotoStorage::url($currentLogoPath) }}" alt="" class="h-16 max-w-full object-contain" />
                @endif

                <input type="file" wire:model="logo" accept="image/*" class="block w-full text-sm text-zinc-500 file:mr-4 file:rounded-full file:border-0 file:bg-zinc-100 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-zinc-700 hover:file:bg-zinc-200 dark:file:bg-zinc-800 dark:file:text-zinc-200" />

                @error('logo')
                    <flux:text class="text-sm text-red-500">{{ $message }}</flux:text>
                @enderror
            </div>

            <div class="flex justify-end gap-2">
                <flux:modal.close>
                    <flux:button variant="ghost">Cancelar</flux:button>
                </flux:modal.close>
                <flux:button type="submit" variant="primary">Guardar</flux:button>
            </div>
        </form>
    </flux:modal>
</div>
