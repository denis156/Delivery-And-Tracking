<?php

namespace App\Livewire\Driver\Pages;

use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;

#[Layout('livewire.layouts.driver')]
#[Title('Profil')]
class Profile extends Component
{
    public function render()
    {
        return view('livewire.driver.pages.profile');
    }
}
