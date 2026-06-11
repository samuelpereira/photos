<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <flux:heading size="xl" level="1">Sessões</flux:heading>
            <flux:subheading>Gerir sessões e respetivas galerias de fotos</flux:subheading>
        </div>

        <flux:button variant="primary" icon="plus" wire:click="create">Nova sessão</flux:button>
    </div>

    <flux:table :paginate="$sessions">
        <flux:table.columns>
            <flux:table.column>Sessão</flux:table.column>
            <flux:table.column>Tipo</flux:table.column>
            <flux:table.column>Data</flux:table.column>
            <flux:table.column>Fotos</flux:table.column>
            <flux:table.column>Estado</flux:table.column>
            <flux:table.column></flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @foreach ($sessions as $session)
                <flux:table.row :key="$session->id">
                    <flux:table.cell variant="strong">{{ $session->name }}</flux:table.cell>
                    <flux:table.cell>
                        <flux:badge size="sm" :color="$session->sessionType->color">
                            {{ $session->typeLabel() }}
                        </flux:badge>
                    </flux:table.cell>
                    <flux:table.cell>{{ $session->session_date->format('d/m/Y') }}</flux:table.cell>
                    <flux:table.cell>{{ $session->photos_count }}</flux:table.cell>
                    <flux:table.cell>
                        <flux:badge size="sm" :color="$session->is_published ? 'green' : 'zinc'">
                            {{ $session->is_published ? 'Publicado' : 'Rascunho' }}
                        </flux:badge>
                    </flux:table.cell>
                    <flux:table.cell align="end">
                        <div class="flex items-center justify-end gap-1">
                            <flux:button size="sm" icon="photo" :href="route('admin.sessions.photos', $session)" wire:navigate>
                                Fotos
                            </flux:button>
                            <flux:button size="sm" variant="ghost" icon="pencil-square" wire:click="edit({{ $session->id }})" />
                            <flux:button
                                size="sm"
                                variant="ghost"
                                icon="trash"
                                wire:click="delete({{ $session->id }})"
                                wire:confirm="Eliminar esta sessão e todas as suas fotos?"
                            />
                        </div>
                    </flux:table.cell>
                </flux:table.row>
            @endforeach
        </flux:table.rows>
    </flux:table>

    <flux:modal name="session-form" class="md:w-[28rem]">
        <form wire:submit="save" class="space-y-6">
            <flux:heading size="lg">{{ $editingId ? 'Editar sessão' : 'Nova sessão' }}</flux:heading>

            <flux:input wire:model="name" label="Nome" placeholder="Ex.: CB Águias vs FC Porto" />

            <flux:select wire:model="type_id" label="Tipo">
                @foreach ($types as $type)
                    <flux:select.option value="{{ $type->id }}">{{ $type->name }}</flux:select.option>
                @endforeach
            </flux:select>

            <flux:input type="date" wire:model="session_date" label="Data da sessão" />

            <flux:textarea wire:model="description" label="Descrição" rows="3" />

            <flux:switch wire:model="is_published" label="Publicado" />

            <div class="flex justify-end gap-2">
                <flux:modal.close>
                    <flux:button variant="ghost">Cancelar</flux:button>
                </flux:modal.close>
                <flux:button type="submit" variant="primary">Guardar</flux:button>
            </div>
        </form>
    </flux:modal>
</div>
