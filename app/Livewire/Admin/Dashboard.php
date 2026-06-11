<?php

namespace App\Livewire\Admin;

use App\Models\Game;
use App\Models\Photo;
use App\Models\Session;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Dashboard')]
class Dashboard extends Component
{
    public function render(): View
    {
        return view('livewire.admin.dashboard', [
            'sessionCount' => Session::query()->count(),
            'photoCount' => Photo::query()->count(),
            'gameCount' => Game::query()->count(),
            'upcomingGames' => Game::query()->upcoming()->limit(5)->get(),
            'gamesWithoutResult' => Game::query()
                ->past()
                ->whereNull('home_score')
                ->limit(5)
                ->get(),
        ]);
    }
}
