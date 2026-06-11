<?php

namespace App\Livewire\Site;

use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.public')]
#[Title('Contactos')]
class Contact extends Component
{
    public function render(): View
    {
        return view('livewire.site.contact', [
            'contact' => config('site.contact'),
        ]);
    }
}
