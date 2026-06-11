<?php

namespace App\Livewire\Site;

use App\Models\Session;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.public')]
class SessionShow extends Component
{
    public Session $session;

    public function mount(Session $session): void
    {
        abort_unless($session->is_published, 404);

        $this->session = $session->load(['photos', 'games', 'sessionType']);
    }

    public function render(): View
    {
        return view('livewire.site.session-show')
            ->title($this->session->name);
    }
}
