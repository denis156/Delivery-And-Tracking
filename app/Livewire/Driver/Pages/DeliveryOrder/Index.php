<?php

namespace App\Livewire\Driver\Pages\DeliveryOrder;

use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;

#[Layout('livewire.layouts.driver')]
#[Title('Daftar Surat Jalan')]
class Index extends Component
{
    public function render()
    {
        return view('livewire.driver.pages.delivery-order.index');
    }
}
