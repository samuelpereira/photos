<?php

use App\Livewire\Admin\Games\Index as GamesIndex;
use App\Livewire\Admin\Sessions\Index as SessionsIndex;
use App\Livewire\Admin\Sessions\Photos as SessionsPhotos;
use App\Livewire\Admin\Settings\Categories\Index as CategoriesIndex;
use App\Livewire\Admin\Settings\Competitions\Index as CompetitionsIndex;
use App\Livewire\Admin\Settings\Teams\Index as TeamsIndex;
use App\Livewire\Admin\Settings\Types\Index as TypesIndex;
use App\Livewire\Admin\Sponsors\Index as SponsorsIndex;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->prefix('dashboard')->group(function () {
    Route::livewire('sessions', SessionsIndex::class)->name('admin.sessions.index');
    Route::livewire('sessions/{session}/photos', SessionsPhotos::class)->name('admin.sessions.photos');
    Route::livewire('jogos', GamesIndex::class)->name('admin.games.index');
    Route::livewire('patrocinadores', SponsorsIndex::class)->name('admin.sponsors.index');

    Route::prefix('configuracao')->group(function () {
        Route::livewire('competicoes', CompetitionsIndex::class)->name('admin.settings.competitions');
        Route::livewire('escaloes', CategoriesIndex::class)->name('admin.settings.categories');
        Route::livewire('equipas', TeamsIndex::class)->name('admin.settings.teams');
        Route::livewire('tipos', TypesIndex::class)->name('admin.settings.types');
    });
});
