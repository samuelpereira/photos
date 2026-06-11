<?php

use App\Livewire\Admin\Dashboard;
use App\Livewire\Site\Contact;
use App\Livewire\Site\GamesIndex;
use App\Livewire\Site\Home;
use App\Livewire\Site\SessionShow;
use App\Livewire\Site\SessionsIndex;
use Illuminate\Support\Facades\Route;

Route::livewire('/', Home::class)->name('home');
Route::livewire('/sessions', SessionsIndex::class)->name('sessions.index');
Route::livewire('/sessions/{session}', SessionShow::class)->name('sessions.show');
Route::livewire('/games', GamesIndex::class)->name('games.index');
Route::livewire('/contact', Contact::class)->name('contact');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::livewire('dashboard', Dashboard::class)->name('dashboard');
});

require __DIR__.'/settings.php';
require __DIR__.'/admin.php';
