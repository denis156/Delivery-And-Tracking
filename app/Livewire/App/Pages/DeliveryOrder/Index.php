<?php

namespace App\Livewire\App\Pages\DeliveryOrder;

use Mary\Traits\Toast;
use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;


#[Title('Daftar Surat Jalan')]
#[Layout('livewire.layouts.app')]
class Index extends Component
{
    use Toast;

    public function render()
    {
        return view('livewire.app.pages.delivery-order.index');
    }
}
