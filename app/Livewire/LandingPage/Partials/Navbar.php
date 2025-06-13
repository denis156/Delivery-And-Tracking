<?php

namespace App\Livewire\LandingPage\Partials;

use Livewire\Component;

class Navbar extends Component
{
    public $isMenuOpen = false;

    public function toggleMenu()
    {
        $this->isMenuOpen = !$this->isMenuOpen;
    }

    public function render()
    {
        return view('livewire.landing-page.partials.navbar');
    }
}
