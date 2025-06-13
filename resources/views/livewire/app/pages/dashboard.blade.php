<div>
    <!-- HEADER -->
    <x-header title="Beranda" separator progress-indicator>
        <x-slot:middle class="!justify-end">
            <x-input placeholder="Search..." wire:model.live.debounce="search" clearable icon="phosphor.magnifying-glass" />
        </x-slot:middle>
        <x-slot:actions>
            <x-button label="Filters" @click="$wire.drawer = true" responsive icon="phosphor.funnel" class="btn-primary" />
        </x-slot:actions>
    </x-header>

    <!-- TABLE  -->
    <x-card>
        <x-table :headers="$headers" :rows="$users" :sort-by="$sortBy">
            @scope('actions', $user)
            <x-button icon="phosphor.trash-simple" wire:click="delete({{ $user['id'] }})" spinner class="btn-ghost btn-sm text-error" />
            @endscope
        </x-table>
    </x-card>

    <!-- FILTER DRAWER -->
    <x-drawer wire:model="drawer" title="Filters" right separator with-close-button class="lg:w-1/3">
        <x-input placeholder="Search..." wire:model.live.debounce="search" icon="phosphor.magnifying-glass" @keydown.enter="$wire.drawer = false" />

        <x-slot:actions>
            <x-button label="Reset" icon="phosphor.x-circle" wire:click="clear" spinner />
            <x-button label="Done" icon="phosphor.check-circle" class="btn-primary" @click="$wire.drawer = false" />
        </x-slot:actions>
    </x-drawer>
</div>
