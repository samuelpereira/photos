<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <flux:heading size="xl" level="1">Escalões</flux:heading>
            <flux:subheading>Escalões associados a cada competição</flux:subheading>
        </div>

        <flux:button variant="primary" icon="plus" wire:click="create" :disabled="$competitions->isEmpty()">
            Novo escalão
        </flux:button>
    </div>

    @if ($competitions->isEmpty())
        <flux:callout variant="warning" icon="exclamation-triangle" heading="Cria primeiro uma competição antes de adicionar escalões." />
    @endif

    <flux:table :paginate="$categories">
        <flux:table.columns>
            <flux:table.column>Escalão</flux:table.column>
            <flux:table.column>Competição</flux:table.column>
            <flux:table.column>Equipas</flux:table.column>
            <flux:table.column></flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @foreach ($categories as $category)
                <flux:table.row :key="$category->id">
                    <flux:table.cell variant="strong">{{ $category->name }}</flux:table.cell>
                    <flux:table.cell>{{ $category->competition->name }}</flux:table.cell>
                    <flux:table.cell>{{ $category->teams_count }}</flux:table.cell>
                    <flux:table.cell align="end">
                        <div class="flex items-center justify-end gap-1">
                            <flux:button size="sm" variant="ghost" icon="pencil-square" wire:click="edit({{ $category->id }})" />
                            <flux:button
                                size="sm"
                                variant="ghost"
                                icon="trash"
                                wire:click="delete({{ $category->id }})"
                                wire:confirm="Eliminar este escalão?"
                            />
                        </div>
                    </flux:table.cell>
                </flux:table.row>
            @endforeach
        </flux:table.rows>
    </flux:table>

    <flux:modal name="category-form" class="md:w-[28rem]">
        <form wire:submit="save" class="space-y-6">
            <flux:heading size="lg">{{ $editingId ? 'Editar escalão' : 'Novo escalão' }}</flux:heading>

            <flux:select wire:model="competition_id" label="Competição">
                @foreach ($competitions as $competition)
                    <flux:select.option value="{{ $competition->id }}">{{ $competition->name }}</flux:select.option>
                @endforeach
            </flux:select>

            <flux:input wire:model="name" label="Nome do escalão" placeholder="Ex.: Seniores M" />

            <div class="flex justify-end gap-2">
                <flux:modal.close>
                    <flux:button variant="ghost">Cancelar</flux:button>
                </flux:modal.close>
                <flux:button type="submit" variant="primary">Guardar</flux:button>
            </div>
        </form>
    </flux:modal>
</div>
