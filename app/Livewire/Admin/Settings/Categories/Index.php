<?php

namespace App\Livewire\Admin\Settings\Categories;

use App\Models\Category;
use App\Models\Competition;
use Flux\Flux;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Escalões')]
class Index extends Component
{
    use WithPagination;

    public ?int $editingId = null;

    public ?int $competition_id = null;

    public string $name = '';

    public function create(): void
    {
        $this->reset(['editingId', 'name']);
        $this->competition_id = Competition::query()->orderBy('name')->value('id');
        $this->resetValidation();

        Flux::modal('category-form')->show();
    }

    public function edit(int $categoryId): void
    {
        $category = Category::query()->findOrFail($categoryId);

        $this->editingId = $category->id;
        $this->competition_id = $category->competition_id;
        $this->name = $category->name;
        $this->resetValidation();

        Flux::modal('category-form')->show();
    }

    public function save(): void
    {
        $validated = $this->validate([
            'competition_id' => ['required', 'exists:competitions,id'],
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('categories', 'name')
                    ->where('competition_id', $this->competition_id)
                    ->ignore($this->editingId),
            ],
        ]);

        if ($this->editingId) {
            Category::query()->findOrFail($this->editingId)->update($validated);
        } else {
            Category::query()->create($validated);
        }

        Flux::modal('category-form')->close();
        Flux::toast(variant: 'success', text: $this->editingId ? 'Escalão atualizado.' : 'Escalão criado.');
    }

    public function delete(int $categoryId): void
    {
        Category::query()->findOrFail($categoryId)->delete();

        Flux::toast(variant: 'success', text: 'Escalão eliminado.');
    }

    public function render(): View
    {
        return view('livewire.admin.settings.categories.index', [
            'categories' => Category::query()
                ->with('competition')
                ->withCount('teams')
                ->orderBy('name')
                ->paginate(10),
            'competitions' => Competition::query()->orderBy('name')->get(),
        ]);
    }
}
