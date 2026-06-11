<?php

namespace App\Livewire\Site;

use App\Models\Session;
use App\Models\SessionType;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.public')]
#[Title('Sessões')]
class SessionsIndex extends Component
{
    use WithPagination;

    #[Url]
    public string $type = '';

    public function setType(string $type): void
    {
        $this->type = $type;
        $this->resetPage();
    }

    public function render(): View
    {
        return view('livewire.site.sessions-index', [
            'sessionTypes' => SessionType::query()->orderBy('name')->get(),
            'sessions' => Session::query()
                ->published()
                ->with(['sessionType', 'coverPhoto', 'photos'])
                ->when($this->type !== '', fn ($query) => $query->whereHas(
                    'sessionType',
                    fn ($typeQuery) => $typeQuery->where('slug', $this->type),
                ))
                ->withCount('photos')
                ->latest('session_date')
                ->paginate(12),
        ]);
    }
}
