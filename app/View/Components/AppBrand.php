<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class AppBrand extends Component
{
    /**
     * Create a new component instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return <<<'HTML'
                <a href="/" wire:navigate>
                    <!-- Hidden when collapsed -->
                    <div {{ $attributes->class(["hidden-when-collapsed"]) }}>
                        <div class="flex items-center gap-2 w-fit">
                            <x-icon name="phosphor.shipping-container" class="w-10 p-1 -mb-1.4 bg-base-300 glass  text-primary rounded-md" />
                            <span class="font-bold text-md me-3 bg-gradient-to-r from-primary via-secondary to-neutral bg-clip-text text-transparent ">
                                {{ config('app.name') }}
                            </span>
                        </div>
                    </div>

                    <!-- Display when collapsed -->
                    <div class="display-when-collapsed hidden mx-5 mt-5 mb-1 h-[28px]">
                        <x-icon name="phosphor.shipping-container" class="w-8 -mb-1.4 text-primary" />
                    </div>
                </a>
            HTML;
    }
}
