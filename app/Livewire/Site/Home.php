<?php

namespace App\Livewire\Site;

use App\Models\Game;
use App\Models\Photo;
use App\Models\Session;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.public')]
#[Title('Início')]
class Home extends Component
{
    public function render(): View
    {
        return view('livewire.site.home', [
            'heroPhoto' => Photo::query()
                ->where('is_featured', true)
                ->whereHas('session', fn ($query) => $query->where('is_published', true))
                ->latest()
                ->first(),
            'weekGames' => Game::query()->thisWeek()->limit(4)->get(),
            'recentSessions' => Session::query()
                ->published()
                ->with(['sessionType', 'coverPhoto', 'photos'])
                ->withCount('photos')
                ->latest('session_date')
                ->limit(6)
                ->get(),
            'recentResults' => Game::query()
                ->past()
                ->whereNotNull('home_score')
                ->with('session')
                ->limit(5)
                ->get(),
        ]);
    }
}
