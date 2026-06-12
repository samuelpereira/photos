<?php

namespace App\Livewire\Admin\Sessions;

use App\Models\Session;
use App\Models\SessionType;
use Flux\Flux;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Str;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Sessões')]
class Index extends Component
{
    use WithPagination;

    public ?int $editingId = null;

    public string $name = '';

    public ?int $type_id = null;

    public string $session_date = '';

    public string $description = '';

    public bool $is_published = true;

    public function create(): void
    {
        $this->reset(['editingId', 'name', 'description']);
        $this->type_id = SessionType::query()->orderBy('name')->value('id');
        $this->session_date = now()->format('Y-m-d');
        $this->is_published = true;
        $this->resetValidation();

        Flux::modal('session-form')->show();
    }

    public function edit(int $sessionId): void
    {
        $session = Session::query()->findOrFail($sessionId);

        $this->editingId = $session->id;
        $this->name = $session->name;
        $this->type_id = $session->type_id;
        $this->session_date = $session->session_date->format('Y-m-d');
        $this->description = $session->description ?? '';
        $this->is_published = $session->is_published;
        $this->resetValidation();

        Flux::modal('session-form')->show();
    }

    public function save(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'type_id' => ['required', 'exists:session_types,id'],
            'session_date' => ['required', 'date'],
            'description' => ['nullable', 'string'],
            'is_published' => ['boolean'],
        ]);

        if ($this->editingId) {
            Session::query()->findOrFail($this->editingId)->update($validated);
        } else {
            Session::query()->create([
                ...$validated,
                'slug' => $this->uniqueSlug($validated['name']),
            ]);
        }

        Flux::modal('session-form')->close();
        Flux::toast(variant: 'success', text: $this->editingId ? 'Sessão atualizada.' : 'Sessão criada.');
    }

    public function delete(int $sessionId): void
    {
        Session::query()->findOrFail($sessionId)->delete();

        Flux::toast(variant: 'success', text: 'Sessão eliminada.');
    }

    private function uniqueSlug(string $name): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $suffix = 2;

        while (Session::query()->where('slug', $slug)->exists()) {
            $slug = $base.'-'.$suffix++;
        }

        return $slug;
    }

    public function render(): View
    {
        return view('livewire.admin.sessions.index', [
            'sessions' => Session::query()
                ->with('sessionType')
                ->withCount('photos')
                ->latest('session_date')
                ->paginate(10),
            'types' => SessionType::query()->orderBy('name')->get(),
        ]);
    }
}
