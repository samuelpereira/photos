<?php

namespace App\Livewire\Admin\Settings\Teams;

use App\Models\Category;
use App\Models\Team;
use Flux\Flux;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Equipas')]
class Index extends Component
{
    use WithPagination;

    public ?int $editingId = null;

    public string $name = '';

    public string $venue = '';

    /** @var array<int> */
    public array $category_ids = [];

    public function create(): void
    {
        $this->reset(['editingId', 'name', 'venue', 'category_ids']);
        $this->resetValidation();

        Flux::modal('team-form')->show();
    }

    public function edit(int $teamId): void
    {
        $team = Team::query()->with('categories')->findOrFail($teamId);

        $this->editingId = $team->id;
        $this->name = $team->name;
        $this->venue = $team->venue ?? '';
        $this->category_ids = $team->categories->pluck('id')->all();
        $this->resetValidation();

        Flux::modal('team-form')->show();
    }

    public function save(): void
    {
        $validated = $this->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                'unique:teams,name,'.($this->editingId ?? 'NULL'),
            ],
            'venue' => ['nullable', 'string', 'max:255'],
            'category_ids' => ['array'],
            'category_ids.*' => ['exists:categories,id'],
        ]);

        $teamData = [
            'name' => $validated['name'],
            'venue' => filled($validated['venue']) ? $validated['venue'] : null,
        ];

        if ($this->editingId) {
            $team = Team::query()->findOrFail($this->editingId);
            $team->update($teamData);
        } else {
            $team = Team::query()->create($teamData);
        }

        $team->categories()->sync($this->category_ids);

        Flux::modal('team-form')->close();
        Flux::toast(variant: 'success', text: $this->editingId ? 'Equipa atualizada.' : 'Equipa criada.');
    }

    public function delete(int $teamId): void
    {
        Team::query()->findOrFail($teamId)->delete();

        Flux::toast(variant: 'success', text: 'Equipa eliminada.');
    }

    public function render(): View
    {
        return view('livewire.admin.settings.teams.index', [
            'teams' => Team::query()
                ->with('categories.competition')
                ->orderBy('name')
                ->paginate(10),
            'categories' => Category::query()
                ->with('competition')
                ->orderBy('name')
                ->get(),
        ]);
    }
}
