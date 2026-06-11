<?php

namespace App\Livewire\Admin\Settings\Competitions;

use App\Models\Competition;
use Flux\Flux;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Competições')]
class Index extends Component
{
    use WithPagination;

    public ?int $editingId = null;

    public string $name = '';

    public function create(): void
    {
        $this->reset(['editingId', 'name']);
        $this->resetValidation();

        Flux::modal('competition-form')->show();
    }

    public function edit(int $competitionId): void
    {
        $competition = Competition::query()->findOrFail($competitionId);

        $this->editingId = $competition->id;
        $this->name = $competition->name;
        $this->resetValidation();

        Flux::modal('competition-form')->show();
    }

    public function save(): void
    {
        $validated = $this->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                'unique:competitions,name,'.($this->editingId ?? 'NULL'),
            ],
        ]);

        if ($this->editingId) {
            Competition::query()->findOrFail($this->editingId)->update($validated);
        } else {
            Competition::query()->create($validated);
        }

        Flux::modal('competition-form')->close();
        Flux::toast(variant: 'success', text: $this->editingId ? 'Competição atualizada.' : 'Competição criada.');
    }

    public function delete(int $competitionId): void
    {
        Competition::query()->findOrFail($competitionId)->delete();

        Flux::toast(variant: 'success', text: 'Competição eliminada.');
    }

    public function render(): View
    {
        return view('livewire.admin.settings.competitions.index', [
            'competitions' => Competition::query()
                ->withCount('categories')
                ->orderBy('name')
                ->paginate(10),
        ]);
    }
}
