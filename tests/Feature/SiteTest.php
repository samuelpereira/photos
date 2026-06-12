<?php

use App\Models\Game;
use App\Models\Photo;
use App\Models\Session;
use App\Models\Sponsor;
use Illuminate\Support\Facades\Storage;

test('homepage banner carousel includes all featured photos', function () {
    config(['photos.disk' => 'public']);
    Storage::fake('public');

    $session = Session::factory()->create();
    $first = Photo::factory()->create([
        'session_id' => $session->id,
        'path' => 'sessions/1/first.jpg',
        'thumbnail_path' => 'sessions/1/first-thumb.jpg',
        'is_featured' => true,
    ]);
    $second = Photo::factory()->create([
        'session_id' => $session->id,
        'path' => 'sessions/1/second.jpg',
        'thumbnail_path' => 'sessions/1/second-thumb.jpg',
        'is_featured' => true,
    ]);

    Storage::disk('public')->put('sessions/1/first-thumb.jpg', 'first');
    Storage::disk('public')->put('sessions/1/second-thumb.jpg', 'second');

    $response = $this->get(route('home'))->assertOk();

    $response->assertSee($first->thumbnailUrl(), false);
    $response->assertSee($second->thumbnailUrl(), false);
    $response->assertSee('Banner 1', false);
    $response->assertSee('Banner 2', false);
});

test('homepage loads with sessions and games', function () {
    $session = Session::factory()->create();
    Photo::factory()->count(2)->create(['session_id' => $session->id, 'is_cover' => true]);
    Game::factory()->upcoming()->create();
    Game::factory()->past()->create(['session_id' => $session->id]);

    $this->get(route('home'))
        ->assertOk()
        ->assertSee($session->name);
});

test('sessions index lists published sessions only', function () {
    $published = Session::factory()->create(['name' => 'Sessão Publicada']);
    Session::factory()->create(['name' => 'Sessão Escondida', 'is_published' => false]);

    $this->get(route('sessions.index'))
        ->assertOk()
        ->assertSee('Sessão Publicada')
        ->assertDontSee('Sessão Escondida');
});

test('session gallery page shows photos', function () {
    $session = Session::factory()->create();
    Photo::factory()->count(3)->create(['session_id' => $session->id]);

    $this->get(route('sessions.show', $session))
        ->assertOk()
        ->assertSee($session->name);
});

test('unpublished session returns 404', function () {
    $session = Session::factory()->create(['is_published' => false]);

    $this->get(route('sessions.show', $session))->assertNotFound();
});

test('contact page loads', function () {
    config([
        'site.contact.email' => 'foto@example.com',
        'site.contact.phone' => '+351 912 345 678',
        'site.contact.location' => 'Porto, Portugal',
    ]);

    $this->get(route('contact'))
        ->assertOk()
        ->assertSee('Contactos')
        ->assertSee('foto@example.com')
        ->assertSee('+351 912 345 678')
        ->assertSee('Porto, Portugal');
});

test('homepage shows active sponsors', function () {
    config(['photos.disk' => 'public']);
    Storage::fake('public');
    Storage::disk('public')->put('sponsors/logo.png', 'logo');

    Sponsor::factory()->create([
        'name' => 'Pingo Doce',
        'url' => 'https://www.pingodoce.pt',
        'logo_path' => 'sponsors/logo.png',
        'is_active' => true,
    ]);

    $this->get(route('home'))
        ->assertOk()
        ->assertSee('Patrocinadores')
        ->assertSee('Pingo Doce');
});

test('games page shows upcoming and past games', function () {
    $upcoming = Game::factory()->upcoming()->create(['home_team' => 'Equipa Futura']);
    $past = Game::factory()->past()->create(['home_team' => 'Equipa Passada']);

    $this->get(route('games.index'))
        ->assertOk()
        ->assertSee('Equipa Futura')
        ->assertSee('Equipa Passada');
});
