<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <flux:heading size="xl" level="1">Equipas</flux:heading>
            <flux:subheading>Equipas, pavilhões e escalões em que participam</flux:subheading>
        </div>

        <flux:button variant="primary" icon="plus" wire:click="create">Nova equipa</flux:button>
    </div>

    <flux:table :paginate="$teams">
        <flux:table.columns>
            <flux:table.column>Equipa</flux:table.column>
            <flux:table.column>Pavilhão</flux:table.column>
            <flux:table.column>Escalões</flux:table.column>
            <flux:table.column></flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @foreach ($teams as $team)
                <flux:table.row :key="$team->id">
                    <flux:table.cell variant="strong">{{ $team->name }}</flux:table.cell>
                    <flux:table.cell>{{ $team->venue ?? '—' }}</flux:table.cell>
                    <flux:table.cell>
                        @if ($team->categories->isEmpty())
                            —
                        @else
                            <div class="flex flex-wrap gap-1">
                                @foreach ($team->categories as $category)
                                    <flux:badge size="sm" color="zinc">
                                        {{ $category->competition->name }} · {{ $category->name }}
                                    </flux:badge>
                                @endforeach
                            </div>
                        @endif
                    </flux:table.cell>
                    <flux:table.cell align="end">
                        <div class="flex items-center justify-end gap-1">
                            <flux:button size="sm" variant="ghost" icon="pencil-square" wire:click="edit({{ $team->id }})" />
                            <flux:button
                                size="sm"
                                variant="ghost"
                                icon="trash"
                                wire:click="delete({{ $team->id }})"
                                wire:confirm="Eliminar esta equipa?"
                            />
                        </div>
                    </flux:table.cell>
                </flux:table.row>
            @endforeach
        </flux:table.rows>
    </flux:table>

    <flux:modal name="team-form" class="md:w-[32rem]">
        <form wire:submit="save" class="space-y-6">
            <flux:heading size="lg">{{ $editingId ? 'Editar equipa' : 'Nova equipa' }}</flux:heading>

            <flux:input wire:model="name" label="Nome da equipa" placeholder="Ex.: CB Águias" />

            <flux:input wire:model="venue" label="Pavilhão" placeholder="Ex.: Pavilhão Municipal" />

            <div class="space-y-3">
                <flux:label>Escalões</flux:label>

                @if ($categories->isEmpty())
                    <flux:text class="text-sm text-zinc-500">Cria escalões antes de associar equipas.</flux:text>
                @else
                    <div class="max-h-48 space-y-2 overflow-y-auto rounded-lg border border-zinc-200 p-3 dark:border-zinc-700">
                        @foreach ($categories as $category)
                            <flux:checkbox
                                wire:model="category_ids"
                                value="{{ $category->id }}"
                                label="{{ $category->competition->name }} · {{ $category->name }}"
                            />
                        @endforeach
                    </div>
                @endif
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
