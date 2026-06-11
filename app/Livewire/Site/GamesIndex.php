<?php

namespace App\Livewire\Site;

use App\Models\Game;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.public')]
#[Title('Jogos')]
class GamesIndex extends Component
{
    use WithPagination;

    public function render(): View
    {
        return view('livewire.site.games-index', [
            'upcomingGames' => Game::query()->upcoming()->get(),
            'pastGames' => Game::query()->past()->with('session')->paginate(10),
        ]);
    }
}
