<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <flux:heading size="xl" level="1">Competições</flux:heading>
            <flux:subheading>Competições disponíveis para jogos e escalões</flux:subheading>
        </div>

        <flux:button variant="primary" icon="plus" wire:click="create">Nova competição</flux:button>
    </div>

    <flux:table :paginate="$competitions">
        <flux:table.columns>
            <flux:table.column>Nome</flux:table.column>
            <flux:table.column>Escalões</flux:table.column>
            <flux:table.column></flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @foreach ($competitions as $competition)
                <flux:table.row :key="$competition->id">
                    <flux:table.cell variant="strong">{{ $competition->name }}</flux:table.cell>
                    <flux:table.cell>{{ $competition->categories_count }}</flux:table.cell>
                    <flux:table.cell align="end">
                        <div class="flex items-center justify-end gap-1">
                            <flux:button size="sm" variant="ghost" icon="pencil-square" wire:click="edit({{ $competition->id }})" />
                            <flux:button
                                size="sm"
                                variant="ghost"
                                icon="trash"
                                wire:click="delete({{ $competition->id }})"
                                wire:confirm="Eliminar esta competição e todos os escalões associados?"
                            />
                        </div>
                    </flux:table.cell>
                </flux:table.row>
            @endforeach
        </flux:table.rows>
    </flux:table>

    <flux:modal name="competition-form" class="md:w-[24rem]">
        <form wire:submit="save" class="space-y-6">
            <flux:heading size="lg">{{ $editingId ? 'Editar competição' : 'Nova competição' }}</flux:heading>

            <flux:input wire:model="name" label="Nome" placeholder="Ex.: Liga Betclic" />

            <div class="flex justify-end gap-2">
                <flux:modal.close>
                    <flux:button variant="ghost">Cancelar</flux:button>
                </flux:modal.close>
                <flux:button type="submit" variant="primary">Guardar</flux:button>
            </div>
        </form>
    </flux:modal>
</div>
