<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <flux:heading size="xl" level="1">Tipos</flux:heading>
            <flux:subheading>Tipos de sessão, como Basquetebol e Dança</flux:subheading>
        </div>

        <flux:button variant="primary" icon="plus" wire:click="create">Novo tipo</flux:button>
    </div>

    <flux:table :paginate="$types">
        <flux:table.columns>
            <flux:table.column>Nome</flux:table.column>
            <flux:table.column>Cor</flux:table.column>
            <flux:table.column>Sessões</flux:table.column>
            <flux:table.column></flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @foreach ($types as $type)
                <flux:table.row :key="$type->id">
                    <flux:table.cell variant="strong">
                        <div class="flex items-center gap-2">
                            <flux:badge size="sm" :color="$type->color">{{ $type->name }}</flux:badge>
                        </div>
                    </flux:table.cell>
                    <flux:table.cell>{{ $colors[$type->color] ?? $type->color }}</flux:table.cell>
                    <flux:table.cell>{{ $type->sessions_count }}</flux:table.cell>
                    <flux:table.cell align="end">
                        <div class="flex items-center justify-end gap-1">
                            <flux:button size="sm" variant="ghost" icon="pencil-square" wire:click="edit({{ $type->id }})" />
                            <flux:button
                                size="sm"
                                variant="ghost"
                                icon="trash"
                                wire:click="delete({{ $type->id }})"
                                wire:confirm="Eliminar este tipo?"
                            />
                        </div>
                    </flux:table.cell>
                </flux:table.row>
            @endforeach
        </flux:table.rows>
    </flux:table>

    <flux:modal name="type-form" class="md:w-[24rem]">
        <form wire:submit="save" class="space-y-6">
            <flux:heading size="lg">{{ $editingId ? 'Editar tipo' : 'Novo tipo' }}</flux:heading>

            <flux:input wire:model="name" label="Nome" placeholder="Ex.: Basquetebol" />

            <flux:select wire:model="color" label="Cor do badge">
                @foreach ($colors as $value => $label)
                    <flux:select.option value="{{ $value }}">{{ $label }}</flux:select.option>
                @endforeach
            </flux:select>

            <div class="flex justify-end gap-2">
                <flux:modal.close>
                    <flux:button variant="ghost">Cancelar</flux:button>
                </flux:modal.close>
                <flux:button type="submit" variant="primary">Guardar</flux:button>
            </div>
        </form>
    </flux:modal>
</div>
