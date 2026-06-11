<?php

use App\Livewire\Admin\Games\Index as GamesIndex;
use App\Livewire\Admin\Sessions\Index as SessionsIndex;
use App\Livewire\Admin\Sessions\Photos as SessionPhotos;
use App\Livewire\Admin\Settings\Categories\Index as CategoriesIndex;
use App\Livewire\Admin\Settings\Competitions\Index as CompetitionsIndex;
use App\Livewire\Admin\Settings\Teams\Index as TeamsIndex;
use App\Livewire\Admin\Settings\Types\Index;
use App\Livewire\Admin\Sponsors\Index as SponsorsIndex;
use App\Models\Category;
use App\Models\Competition;
use App\Models\Game;
use App\Models\Photo;
use App\Models\Session;
use App\Models\SessionType;
use App\Models\Sponsor;
use App\Models\Team;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

beforeEach(function () {
    $this->actingAs(User::factory()->create());
});

test('admin pages require authentication', function (string $routeName) {
    auth()->logout();

    $this->get(route($routeName))->assertRedirect(route('login'));
})->with([
    'admin.sessions.index',
    'admin.games.index',
    'admin.settings.competitions',
    'admin.settings.categories',
    'admin.settings.teams',
    'admin.settings.types',
    'admin.sponsors.index',
]);

test('admin can view sessions list', function () {
    $session = Session::factory()->create();

    $this->get(route('admin.sessions.index'))
        ->assertOk()
        ->assertSee($session->name);
});

test('admin can create a session', function () {
    $type = SessionType::factory()->create(['slug' => 'basquetebol']);

    Livewire::test(SessionsIndex::class)
        ->call('create')
        ->set('name', 'Novo Torneio')
        ->set('type_id', $type->id)
        ->set('session_date', '2026-06-15')
        ->call('save')
        ->assertHasNoErrors();

    expect(Session::query()->where('slug', 'novo-torneio')->exists())->toBeTrue();
});

test('admin can manage session types', function () {
    Livewire::test(Index::class)
        ->call('create')
        ->set('name', 'Futebol')
        ->set('color', 'green')
        ->call('save')
        ->assertHasNoErrors();

    expect(SessionType::query()->where('slug', 'futebol')->exists())->toBeTrue();
});

test('admin can delete a session', function () {
    $session = Session::factory()->create();

    Livewire::test(SessionsIndex::class)->call('delete', $session->id);

    expect(Session::query()->find($session->id))->toBeNull();
});

test('admin can create competition, category and team', function () {
    Livewire::test(CompetitionsIndex::class)
        ->call('create')
        ->set('name', 'Liga Betclic')
        ->call('save')
        ->assertHasNoErrors();

    $competition = Competition::query()->where('name', 'Liga Betclic')->first();

    Livewire::test(CategoriesIndex::class)
        ->call('create')
        ->set('competition_id', $competition->id)
        ->set('name', 'Seniores M')
        ->call('save')
        ->assertHasNoErrors();

    $category = Category::query()->where('name', 'Seniores M')->first();

    Livewire::test(TeamsIndex::class)
        ->call('create')
        ->set('name', 'CB Águias')
        ->set('venue', 'Pavilhão Municipal')
        ->set('category_ids', [$category->id])
        ->call('save')
        ->assertHasNoErrors();

    $team = Team::query()->where('name', 'CB Águias')->first();

    expect($team)->not->toBeNull()
        ->and($team->categories)->toHaveCount(1);
});

test('admin can create a game with configuration and venue from home team', function () {
    $competition = Competition::factory()->create(['name' => 'Liga Betclic']);
    $category = Category::factory()->create([
        'competition_id' => $competition->id,
        'name' => 'Seniores M',
    ]);

    $home = Team::factory()->create([
        'name' => 'CB Águias',
        'venue' => 'Pavilhão Municipal',
    ]);
    $away = Team::factory()->create(['name' => 'FC Porto', 'venue' => 'Dragão Arena']);

    $home->categories()->attach($category->id);
    $away->categories()->attach($category->id);

    Livewire::test(GamesIndex::class)
        ->call('create')
        ->set('competition_id', $competition->id)
        ->set('category_id', $category->id)
        ->set('home_team_id', $home->id)
        ->set('away_team_id', $away->id)
        ->set('tip_off_at', '2026-06-01T18:00')
        ->set('home_score', 87)
        ->set('away_score', 92)
        ->call('save')
        ->assertHasNoErrors();

    $game = Game::query()->where('home_team_id', $home->id)->first();

    expect($game)->not->toBeNull()
        ->and($game->hasResult())->toBeTrue()
        ->and($game->venue)->toBe('Pavilhão Municipal')
        ->and($game->competition)->toBe('Liga Betclic')
        ->and($game->category)->toBe('Seniores M');
});

test('venue can be overridden when creating a game', function () {
    $competition = Competition::factory()->create();
    $category = Category::factory()->create(['competition_id' => $competition->id]);

    $home = Team::factory()->create(['venue' => 'Pavilhão Municipal']);
    $away = Team::factory()->create();

    $home->categories()->attach($category->id);
    $away->categories()->attach($category->id);

    Livewire::test(GamesIndex::class)
        ->call('create')
        ->set('competition_id', $competition->id)
        ->set('category_id', $category->id)
        ->set('home_team_id', $home->id)
        ->set('away_team_id', $away->id)
        ->set('venue', 'Pavilhão Alternativo')
        ->set('tip_off_at', '2026-06-01T18:00')
        ->call('save')
        ->assertHasNoErrors();

    expect(Game::query()->first()->venue)->toBe('Pavilhão Alternativo');
});

test('manage photos page loads', function () {
    $session = Session::factory()->create();

    $this->get(route('admin.sessions.photos', $session))->assertOk();
});

test('admin can reorder photos', function () {
    $session = Session::factory()->create();
    $photos = Photo::factory()
        ->count(3)
        ->sequence(
            ['sort_order' => 0],
            ['sort_order' => 1],
            ['sort_order' => 2],
        )
        ->create(['session_id' => $session->id]);

    $ids = $photos->pluck('id')->all();

    Livewire::test(SessionPhotos::class, ['session' => $session])
        ->call('reorderPhotos', [$ids[2], $ids[0], $ids[1]])
        ->assertHasNoErrors();

    expect(Photo::query()->find($ids[2])->sort_order)->toBe(0);
    expect(Photo::query()->find($ids[0])->sort_order)->toBe(1);
    expect(Photo::query()->find($ids[1])->sort_order)->toBe(2);
});

test('admin can manage sponsors', function () {
    config(['photos.disk' => 'public']);
    Storage::fake('public');

    Livewire::test(SponsorsIndex::class)
        ->call('create')
        ->set('name', 'Pingo Doce')
        ->set('url', 'https://www.pingodoce.pt')
        ->set('logo', UploadedFile::fake()->image('logo.png', 200, 80))
        ->call('save')
        ->assertHasNoErrors();

    expect(Sponsor::query()->where('name', 'Pingo Doce')->exists())->toBeTrue();
});
