<?php

namespace App\Livewire\Admin\Settings\Types;

use App\Models\SessionType;
use Flux\Flux;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Tipos')]
class Index extends Component
{
    use WithPagination;

    public ?int $editingId = null;

    public string $name = '';

    public string $color = 'zinc';

    public function create(): void
    {
        $this->reset(['editingId', 'name']);
        $this->color = 'zinc';
        $this->resetValidation();

        Flux::modal('type-form')->show();
    }

    public function edit(int $typeId): void
    {
        $type = SessionType::query()->findOrFail($typeId);

        $this->editingId = $type->id;
        $this->name = $type->name;
        $this->color = $type->color;
        $this->resetValidation();

        Flux::modal('type-form')->show();
    }

    public function save(): void
    {
        $validated = $this->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('session_types', 'name')->ignore($this->editingId),
            ],
            'color' => ['required', Rule::in(array_keys(SessionType::COLORS))],
        ]);

        $payload = [
            'name' => $validated['name'],
            'slug' => $this->uniqueSlug($validated['name']),
            'color' => $validated['color'],
        ];

        if ($this->editingId) {
            SessionType::query()->findOrFail($this->editingId)->update($payload);
        } else {
            SessionType::query()->create($payload);
        }

        Flux::modal('type-form')->close();
        Flux::toast(variant: 'success', text: $this->editingId ? 'Tipo atualizado.' : 'Tipo criado.');
    }

    public function delete(int $typeId): void
    {
        $type = SessionType::query()->withCount('sessions')->findOrFail($typeId);

        if ($type->sessions_count > 0) {
            Flux::toast(variant: 'danger', text: 'Não é possível eliminar um tipo com sessões associadas.');

            return;
        }

        $type->delete();

        Flux::toast(variant: 'success', text: 'Tipo eliminado.');
    }

    private function uniqueSlug(string $name): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $suffix = 2;

        while (SessionType::query()->where('slug', $slug)->where('id', '!=', $this->editingId)->exists()) {
            $slug = $base.'-'.$suffix++;
        }

        return $slug;
    }

    public function render(): View
    {
        return view('livewire.admin.settings.types.index', [
            'types' => SessionType::query()
                ->withCount('sessions')
                ->orderBy('name')
                ->paginate(10),
            'colors' => SessionType::COLORS,
        ]);
    }
}
