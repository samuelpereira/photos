<?php

namespace App\Livewire\Admin\Games;

use App\Models\Category;
use App\Models\Competition;
use App\Models\Game;
use App\Models\Session;
use App\Models\Team;
use Flux\Flux;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Jogos')]
class Index extends Component
{
    use WithPagination;

    public ?int $editingId = null;

    public ?int $competition_id = null;

    public ?int $category_id = null;

    public ?int $home_team_id = null;

    public ?int $away_team_id = null;

    public string $home_team = '';

    public string $away_team = '';

    public string $venue = '';

    public string $tip_off_at = '';

    public ?int $home_score = null;

    public ?int $away_score = null;

    public ?int $session_id = null;

    public function create(): void
    {
        $this->reset([
            'editingId',
            'competition_id',
            'category_id',
            'home_team_id',
            'away_team_id',
            'home_team',
            'away_team',
            'venue',
            'home_score',
            'away_score',
            'session_id',
        ]);
        $this->tip_off_at = now()->addDay()->setTime(18, 0)->format('Y-m-d\TH:i');
        $this->resetValidation();

        Flux::modal('game-form')->show();
    }

    public function edit(Game $game): void
    {
        $this->editingId = $game->id;
        $this->competition_id = $game->competition_id;
        $this->category_id = $game->category_id;
        $this->home_team_id = $game->home_team_id;
        $this->away_team_id = $game->away_team_id;
        $this->home_team = $game->home_team;
        $this->away_team = $game->away_team;
        $this->venue = $game->venue ?? '';
        $this->tip_off_at = $game->tip_off_at->format('Y-m-d\TH:i');
        $this->home_score = $game->home_score;
        $this->away_score = $game->away_score;
        $this->session_id = $game->session_id;
        $this->resetValidation();

        Flux::modal('game-form')->show();
    }

    public function updatedCompetitionId(): void
    {
        $this->category_id = null;
        $this->home_team_id = null;
        $this->away_team_id = null;
        $this->home_team = '';
        $this->away_team = '';
    }

    public function updatedCategoryId(): void
    {
        $this->home_team_id = null;
        $this->away_team_id = null;
        $this->home_team = '';
        $this->away_team = '';
    }

    public function updatedHomeTeamId(?int $value): void
    {
        if (! $value) {
            $this->home_team = '';

            return;
        }

        $team = Team::query()->find($value);

        if (! $team) {
            return;
        }

        $this->home_team = $team->name;

        if (filled($team->venue)) {
            $this->venue = $team->venue;
        }
    }

    public function updatedAwayTeamId(?int $value): void
    {
        if (! $value) {
            $this->away_team = '';

            return;
        }

        $team = Team::query()->find($value);

        if ($team) {
            $this->away_team = $team->name;
        }
    }

    public function save(): void
    {
        $validated = $this->validate([
            'competition_id' => ['required', 'exists:competitions,id'],
            'category_id' => ['required', 'exists:categories,id'],
            'home_team_id' => ['required', 'exists:teams,id', 'different:away_team_id'],
            'away_team_id' => ['required', 'exists:teams,id'],
            'venue' => ['nullable', 'string', 'max:255'],
            'tip_off_at' => ['required', 'date'],
            'home_score' => ['nullable', 'integer', 'min:0', 'max:300', 'required_with:away_score'],
            'away_score' => ['nullable', 'integer', 'min:0', 'max:300', 'required_with:home_score'],
            'session_id' => ['nullable', 'exists:sessions,id'],
        ]);

        /** @var Competition $competition */
        $competition = Competition::query()->findOrFail($validated['competition_id']);
        /** @var Category $category */
        $category = Category::query()->findOrFail($validated['category_id']);
        /** @var Team $homeTeam */
        $homeTeam = Team::query()->findOrFail($validated['home_team_id']);
        /** @var Team $awayTeam */
        $awayTeam = Team::query()->findOrFail($validated['away_team_id']);

        $payload = [
            ...$validated,
            'home_team' => $homeTeam->name,
            'away_team' => $awayTeam->name,
            'competition' => $competition->name,
            'category' => $category->name,
            'venue' => filled($validated['venue']) ? $validated['venue'] : null,
        ];

        if ($this->editingId) {
            Game::query()->findOrFail($this->editingId)->update($payload);
        } else {
            Game::query()->create($payload);
        }

        Flux::modal('game-form')->close();
        Flux::toast(variant: 'success', text: $this->editingId ? 'Jogo atualizado.' : 'Jogo criado.');
    }

    public function delete(Game $game): void
    {
        $game->delete();

        Flux::toast(variant: 'success', text: 'Jogo eliminado.');
    }

    /** @return Collection<int, Team> */
    private function availableTeams(): Collection
    {
        if (! $this->category_id) {
            return collect();
        }

        $category = Category::query()
            ->with('teams')
            ->find($this->category_id);

        return $category ? $category->teams : collect();
    }

    public function render(): View
    {
        return view('livewire.admin.games.index', [
            'games' => Game::query()
                ->with('session')
                ->orderByDesc('tip_off_at')
                ->paginate(10),
            'sessions' => Session::query()->latest('session_date')->get(['id', 'name']),
            'competitions' => Competition::query()->orderBy('name')->get(),
            'categories' => $this->competition_id
                ? Category::query()->where('competition_id', $this->competition_id)->orderBy('name')->get()
                : collect(),
            'teams' => $this->availableTeams(),
        ]);
    }
}
