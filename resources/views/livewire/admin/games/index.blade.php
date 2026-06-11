<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <flux:heading size="xl" level="1">Jogos</flux:heading>
            <flux:subheading>Gerir o calendário e os resultados</flux:subheading>
        </div>

        <flux:button variant="primary" icon="plus" wire:click="create" :disabled="$competitions->isEmpty()">
            Novo jogo
        </flux:button>
    </div>

    @if ($competitions->isEmpty())
        <flux:callout variant="warning" icon="exclamation-triangle" heading="Configura competições, escalões e equipas antes de criar jogos." />
    @endif

    <flux:table :paginate="$games">
        <flux:table.columns>
            <flux:table.column>Jogo</flux:table.column>
            <flux:table.column>Competição</flux:table.column>
            <flux:table.column>Data</flux:table.column>
            <flux:table.column>Resultado</flux:table.column>
            <flux:table.column>Sessão</flux:table.column>
            <flux:table.column></flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @foreach ($games as $game)
                <flux:table.row :key="$game->id">
                    <flux:table.cell variant="strong">{{ $game->home_team }} vs {{ $game->away_team }}</flux:table.cell>
                    <flux:table.cell>
                        {{ $game->competition ?? '—' }}
                        @if ($game->category)
                            <span class="text-zinc-400">· {{ $game->category }}</span>
                        @endif
                    </flux:table.cell>
                    <flux:table.cell>
                        <div>{{ $game->tip_off_at->format('d/m/Y H:i') }}</div>
                        @if ($game->venue)
                            <div class="text-xs text-zinc-500">{{ $game->venue }}</div>
                        @endif
                    </flux:table.cell>
                    <flux:table.cell>
                        @if ($game->hasResult())
                            <flux:badge size="sm" color="orange">{{ $game->home_score }}–{{ $game->away_score }}</flux:badge>
                        @elseif ($game->isPast())
                            <flux:badge size="sm" color="red">Sem resultado</flux:badge>
                        @else
                            <flux:badge size="sm" color="blue">Agendado</flux:badge>
                        @endif
                    </flux:table.cell>
                    <flux:table.cell>{{ $game->session?->name ?? '—' }}</flux:table.cell>
                    <flux:table.cell align="end">
                        <div class="flex items-center justify-end gap-1">
                            <flux:button size="sm" variant="ghost" icon="pencil-square" wire:click="edit({{ $game->id }})" />
                            <flux:button
                                size="sm"
                                variant="ghost"
                                icon="trash"
                                wire:click="delete({{ $game->id }})"
                                wire:confirm="Eliminar este jogo?"
                            />
                        </div>
                    </flux:table.cell>
                </flux:table.row>
            @endforeach
        </flux:table.rows>
    </flux:table>

    <flux:modal name="game-form" class="md:w-[36rem]">
        <form wire:submit="save" class="space-y-6">
            <flux:heading size="lg">{{ $editingId ? 'Editar jogo' : 'Novo jogo' }}</flux:heading>

            <div class="grid gap-4 sm:grid-cols-2">
                <flux:select wire:model.live="competition_id" label="Competição">
                    <flux:select.option value="">Selecionar&hellip;</flux:select.option>
                    @foreach ($competitions as $competition)
                        <flux:select.option value="{{ $competition->id }}">{{ $competition->name }}</flux:select.option>
                    @endforeach
                </flux:select>

                <flux:select wire:model.live="category_id" label="Escalão" :disabled="! $competition_id">
                    <flux:select.option value="">Selecionar&hellip;</flux:select.option>
                    @foreach ($categories as $category)
                        <flux:select.option value="{{ $category->id }}">{{ $category->name }}</flux:select.option>
                    @endforeach
                </flux:select>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <flux:select wire:model.live="home_team_id" label="Equipa da casa" :disabled="! $category_id">
                    <flux:select.option value="">Selecionar&hellip;</flux:select.option>
                    @foreach ($teams as $team)
                        <flux:select.option value="{{ $team->id }}">{{ $team->name }}</flux:select.option>
                    @endforeach
                </flux:select>

                <flux:select wire:model.live="away_team_id" label="Equipa visitante" :disabled="! $category_id">
                    <flux:select.option value="">Selecionar&hellip;</flux:select.option>
                    @foreach ($teams as $team)
                        <flux:select.option value="{{ $team->id }}">{{ $team->name }}</flux:select.option>
                    @endforeach
                </flux:select>
            </div>

            @if ($category_id && $teams->isEmpty())
                <flux:callout variant="warning" icon="exclamation-triangle" heading="Não há equipas neste escalão. Adiciona equipas em Configuração." />
            @endif

            <div class="grid gap-4 sm:grid-cols-2">
                <flux:input type="datetime-local" wire:model="tip_off_at" label="Data e hora" />
                <flux:input wire:model="venue" label="Pavilhão" description="Preenchido automaticamente com o pavilhão da equipa da casa, mas pode ser alterado." />
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <flux:input type="number" min="0" wire:model="home_score" label="Pontos (casa)" placeholder="—" />
                <flux:input type="number" min="0" wire:model="away_score" label="Pontos (fora)" placeholder="—" />
            </div>

            <flux:select wire:model="session_id" label="Sessão de fotos (opcional)">
                <flux:select.option value="">Sem sessão</flux:select.option>
                @foreach ($sessions as $session)
                    <flux:select.option value="{{ $session->id }}">{{ $session->name }}</flux:select.option>
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
