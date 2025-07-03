{{-- Client Index Page - Mary UI + DaisyUI Standards --}}
<div>
    {{-- HEADER --}}
    <x-header title="{{ \App\Class\Helper\ClientHelper::PAGE_TITLE_INDEX }}" subtitle="{{ \App\Class\Helper\ClientHelper::PAGE_SUBTITLE_INDEX }}" icon="phosphor.user-duotone"
        icon-classes="text-primary h-10" separator progress-indicator>
        <x-slot:middle class="!justify-end">
            <x-input placeholder="{{ \App\Class\Helper\FormatHelper::PLACEHOLDER_SEARCH_CLIENT }}" wire:model.live.debounce="search" clearable
                icon="{{ $this->clientUIConfig['icons']['search'] }}" />
        </x-slot:middle>
        <x-slot:actions>
            <x-button label="{{ \App\Class\Helper\FormatHelper::LABEL_FILTER }}" @click="$wire.drawer = true" responsive icon="{{ $this->clientUIConfig['icons']['filter'] }}" class="btn-primary" />
            <x-button label="{{ \App\Class\Helper\FormatHelper::LABEL_ADD }} Client" link="{{ route('app.client.create') }}" responsive
                icon="{{ $this->clientUIConfig['icons']['add'] }}" class="btn-success" />
        </x-slot:actions>
    </x-header>

    {{-- STATISTICS CARDS --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-2 md:gap-4 mb-6">
        <x-stat title="{{ \App\Class\Helper\ClientHelper::STAT_TOTAL_CLIENTS }}" :value="$this->clientStats['totalClients']" icon="{{ $this->clientUIConfig['icons']['user'] }}" color="text-primary"
            class="bg-primary/20 hover:shadow-xl hover:shadow-primary transition-all duration-300" :tooltip="$this->clientStats['totalClientsTooltip']" />
        <x-stat title="{{ \App\Class\Helper\ClientHelper::STAT_ACTIVE_CLIENTS }}" :value="$this->clientStats['activeClients']" icon="{{ $this->clientUIConfig['icons']['success'] }}" color="text-success"
            class="bg-success/20 hover:shadow-xl hover:shadow-primary transition-all duration-300" :tooltip="$this->clientStats['activeClientsTooltip']" />
        <x-stat title="{{ \App\Class\Helper\ClientHelper::STAT_INACTIVE_CLIENTS }}" :value="$this->clientStats['inactiveClients']" icon="{{ $this->clientUIConfig['icons']['error'] }}" color="text-warning"
            class="text-warning bg-warning/20 hover:shadow-xl hover:shadow-primary transition-all duration-300" :tooltip="$this->clientStats['inactiveClientsTooltip']" />
    </div>

    {{-- MAIN CONTENT --}}
    @if ($this->clients->count() === 0)
        {{-- EMPTY STATE --}}
        <x-card class="p-12">
            <div class="text-center">
                <x-icon name="{{ $this->clientUIConfig['icons']['user'] }}" class="w-16 h-16 mx-auto text-base-content/30 mb-4" />
                <h3 class="text-lg font-semibold text-base-content/60 mb-2">{{ \App\Class\Helper\ClientHelper::EMPTY_NO_CLIENTS }}</h3>
                <p class="text-base-content/40 mb-6">
                    @if ($search)
                        {{ \App\Class\Helper\ClientHelper::EMPTY_SEARCH_NO_RESULTS }} "<strong>{{ $search }}</strong>"
                    @else
                        {{ \App\Class\Helper\ClientHelper::EMPTY_NO_CLIENTS_DESC }}
                    @endif
                </p>
                @if (!$search)
                    <x-button label="{{ \App\Class\Helper\FormatHelper::LABEL_ADD }} Client Pertama" link="{{ route('app.client.create') }}" icon="{{ $this->clientUIConfig['icons']['add'] }}"
                        class="btn-primary" />
                @else
                    <x-button label="{{ \App\Class\Helper\FormatHelper::LABEL_RESET }} Pencarian" wire:click="$set('search', '')" icon="{{ $this->clientUIConfig['icons']['close'] }}"
                        class="btn-ghost" />
                @endif
            </div>
        </x-card>
    @else
        {{-- CLIENT CARDS --}}
        <x-card class="p-6 shadow-md">
        {{-- ACTIVE FILTERS INFO --}}
        @if ($this->hasActiveFilters)
            <x-card class="p-4 mb-4 bg-base-200">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="text-sm font-medium text-base-content/70">Filter aktif:</span>
                    @foreach ($this->activeFiltersInfo as $filter)
                        <x-badge :label="$filter['label'] . ': ' . $filter['value']" icon="{{ $filter['icon'] }}" class="badge-primary badge-outline" />
                    @endforeach
                    <x-button label="{{ \App\Class\Helper\FormatHelper::LABEL_RESET }} Filter" wire:click="clear" icon="{{ $this->clientUIConfig['icons']['close'] }}"
                        class="btn-ghost btn-sm ml-2" />
                </div>
            </x-card>
        @endif

            {{-- Grid Layout menggunakan DaisyUI responsive grid --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2 md:gap-4">
                @foreach ($this->clients as $client)
                    @php
                        $clientData = $client->client;
                        $statusColor = $client->is_active ? 'success' : 'warning';
                        $statusIcon = $client->is_active ? $this->clientUIConfig['icons']['success'] : $this->clientUIConfig['icons']['error'];
                        $statusLabel = $client->is_active ? 'Aktif' : 'Nonaktif';
                    @endphp

                    {{-- Client Card menggunakan Model Accessors --}}
                    <x-card title="{{ $client->name }}"
                        class="bg-base-200 hover:shadow-xl hover:shadow-primary transition-all duration-300"
                        wire:click="viewClient({{ $client->id }})" no-separator>
                        {{-- DYNAMIC: Status Badge di Menu menggunakan Model Accessor --}}
                        <x-slot:menu>
                            <div aria-label="{{ $statusColor }}"
                                class="status status-{{ $statusColor }} animate-pulse"></div>
                            <span class="text-md text-{{ $statusColor }} font-mono">
                                {{ $statusLabel }}
                            </span>
                        </x-slot:menu>

                        {{-- Card Content --}}
                        <div class="flex w-full">
                            {{-- Avatar menggunakan Model Accessor --}}
                            <div class="grid place-items-center">
                                @if ($client->avatar_url)
                                    <x-avatar :src="Storage::url($client->avatar_url)"
                                        class="w-10 md:w-20 ring-{{ $statusColor }} ring-offset-base-100 rounded-full ring-2 ring-offset-2" />
                                @else
                                    <x-avatar :placeholder="substr($client->name, 0, 2)"
                                        class="w-10 md:w-20 ring-{{ $statusColor }} ring-offset-base-100 rounded-full ring-2 ring-offset-2" />
                                @endif
                            </div>

                            {{-- Divider --}}
                            <div class="divider divider-horizontal"></div>

                            {{-- Client Information - TWO COLUMN LAYOUT --}}
                            <div class="card bg-base-300 rounded-box grow p-3 shadow-md overflow-visible">
                                <div class="flex flex-col space-y-2">
                                    {{-- DYNAMIC: Client Badge & Company Info --}}
                                    <div class="flex flex-col md:flex-row justify-center items-center gap-2 mb-2">
                                        {{-- Client Role Badge dengan helper --}}
                                        <div class="badge badge-{{ $this->clientUIConfig['colors']['client_role'] }} badge-xs md:badge-sm lg:badge-md relative">
                                            <x-icon name="{{ $this->clientUIConfig['icons']['user'] }}" class="h-2 md:h-4" />
                                            Client
                                        </div>

                                        {{-- DYNAMIC: Company Badge --}}
                                        @if ($clientData && $clientData->company_code)
                                            <div class="badge badge-{{ $this->clientUIConfig['colors']['company_code'] }} badge-xs md:badge-sm lg:badge-md relative">
                                                <x-icon name="{{ $this->clientUIConfig['icons']['company_code'] }}" class="h-2 md:h-3" />
                                                {{ $clientData->company_code }}
                                            </div>
                                        @endif
                                    </div>

                                    {{-- Contact & Company Information - Two Column Layout --}}
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-xs">
                                        {{-- Left Column --}}
                                        <div class="space-y-2">
                                            {{-- DYNAMIC: Email dengan icon dari UI config --}}
                                            <div class="flex items-center gap-1">
                                                <x-icon name="{{ $this->clientUIConfig['icons']['email'] }}"
                                                    class="h-2 md:h-4 flex-shrink-0" />
                                                <span class="truncate text-xs md:text-sm text-base-content/80"
                                                    title="{{ $client->email }}">
                                                    {{ $client->email }}
                                                </span>
                                            </div>

                                            {{-- DYNAMIC: Company Phone dengan icon dari UI config --}}
                                            @if ($clientData && $clientData->phone)
                                                <div class="flex items-center gap-1">
                                                    <x-icon name="{{ $this->clientUIConfig['icons']['phone'] }}" class="h-2 md:h-4 flex-shrink-0" />
                                                    <span class="truncate text-xs md:text-sm text-base-content/80"
                                                        title="{{ $clientData->formatted_phone }}">
                                                        {{ $clientData->formatted_phone }}
                                                    </span>
                                                </div>
                                            @endif
                                        </div>

                                        {{-- Right Column --}}
                                        <div class="space-y-2">
                                            {{-- DYNAMIC: Company Name dengan icon dari UI config --}}
                                            @if ($clientData && $clientData->company_name)
                                                <div class="flex items-center gap-1">
                                                    <x-icon name="{{ $this->clientUIConfig['icons']['company_name'] }}"
                                                        class="h-2 md:h-4 flex-shrink-0" />
                                                    <span class="truncate text-xs md:text-sm text-base-content/80"
                                                        title="{{ $clientData->company_name }}">
                                                        {{ $clientData->company_name }}
                                                    </span>
                                                </div>
                                            @endif

                                            {{-- DYNAMIC: Contact Person dengan icon dari UI config --}}
                                            @if ($clientData && $clientData->contact_person)
                                                <div class="flex items-center gap-1">
                                                    <x-icon name="{{ $this->clientUIConfig['icons']['contact_person'] }}" class="h-2 md:h-4 flex-shrink-0" />
                                                    <span class="truncate text-xs md:text-sm text-base-content/80"
                                                        title="{{ $clientData->contact_person }}">
                                                        {{ $clientData->contact_person }}
                                                    </span>
                                                </div>
                                            @endif
                                        </div>

                                        {{-- Full Width Address --}}
                                        <div class="col-span-full">
                                            @if ($clientData && $clientData->company_address)
                                                <div class="flex items-center gap-1">
                                                    <x-icon name="{{ $this->clientUIConfig['icons']['company_address'] }}"
                                                        class="h-2 md:h-4 flex-shrink-0" />
                                                    <span class="truncate text-xs md:text-sm text-base-content/80">
                                                        {{ $clientData->short_address }}
                                                    </span>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Action Buttons --}}
                        <x-slot:actions separator>
                            {{-- View Button --}}
                            <x-button label="Lihat" wire:click.stop="viewClient({{ $client->id }})"
                                class="btn-info btn-md" icon="phosphor.eye" responsive
                                tooltip="Lihat detail {{ $client->name }}" />

                            {{-- Change Status Button menggunakan Model Accessor --}}
                            <x-button :label="$client->is_active ? 'Nonaktifkan' : 'Aktifkan'" wire:click.stop="openChangeStatusModal({{ $client->id }})"
                                class="btn-{{ $client->is_active ? 'warning' : 'success' }} btn-md" :icon="$client->is_active ? \App\Class\Helper\UserHelper::getStatusIcon('inactive') : \App\Class\Helper\UserHelper::getStatusIcon('active')"
                                responsive :tooltip="$client->is_active ? 'Nonaktifkan akun client' : 'Aktifkan akun client'" />

                            {{-- Delete Button --}}
                            <x-button label="Hapus" wire:click.stop="openDeleteModal({{ $client->id }})"
                                class="btn-error btn-md" icon="phosphor.trash" responsive
                                tooltip="Hapus {{ $client->name }}" />
                        </x-slot:actions>
                    </x-card>
                @endforeach
            </div>

            {{-- Pagination sesuai Mary UI + DaisyUI Standards --}}
            @if ($this->clients->hasPages())
                <div class="mt-8 border-t border-base-300 pt-6">
                    {{-- Results Info & Per Page Selector --}}
                    <div
                        class="flex flex-col md:flex-row md:justify-between md:items-center space-y-4 md:space-y-0 mb-6">
                        {{-- Results Info --}}
                        <div class="text-sm text-base-content/70 text-center md:text-left">
                            Menampilkan <span class="font-semibold">{{ $this->clients->firstItem() ?? 0 }}</span> -
                            <span class="font-semibold">{{ $this->clients->lastItem() ?? 0 }}</span>
                            dari <span class="font-semibold">{{ $this->clients->total() }}</span> client
                            @if ($search)
                                <br class="md:hidden">
                                <span class="block md:inline mt-1 md:mt-0">
                                    untuk pencarian "<span class="font-semibold text-primary">{{ $search }}</span>"
                                </span>
                            @endif
                        </div>

                        {{-- Per Page Selector --}}
                        <div class="flex items-center justify-center md:justify-end gap-3">
                            <label class="text-sm text-base-content/70 font-medium">Per halaman:</label>
                            <x-select wire:model.live="perPage" :options="$this->perPageOptions" option-value="value"
                                option-label="label" class="select-sm w-20" />
                        </div>
                    </div>

                    {{-- Custom Pagination dengan DaisyUI join components --}}
                    <div class="flex justify-center">
                        <div class="join shadow-sm">
                            {{-- Previous Button --}}
                            @if ($this->clients->onFirstPage())
                                <button class="join-item btn btn-sm btn-disabled">
                                    <x-icon name="phosphor.caret-left" class="w-3 h-3 md:w-4 md:h-4" />
                                    <span class="hidden sm:inline ml-1">Prev</span>
                                </button>
                            @else
                                <button wire:click="previousPage" class="join-item btn btn-sm hover:btn-primary">
                                    <x-icon name="phosphor.caret-left" class="w-3 h-3 md:w-4 md:h-4" />
                                    <span class="hidden sm:inline ml-1">Prev</span>
                                </button>
                            @endif

                            {{-- Page Numbers --}}
                            @foreach ($this->paginationPages as $page)
                                @if ($page['type'] === 'page')
                                    @if ($page['current'])
                                        <button class="join-item btn btn-sm btn-primary min-w-[2.5rem]">{{ $page['page'] }}</button>
                                    @else
                                        <button wire:click="gotoPage({{ $page['page'] }})"
                                            class="join-item btn btn-sm hover:btn-primary min-w-[2.5rem]">{{ $page['page'] }}</button>
                                    @endif
                                @elseif ($page['type'] === 'dots')
                                    <span class="join-item btn btn-sm btn-disabled cursor-default hidden md:flex">...</span>
                                @endif
                            @endforeach

                            {{-- Next Button --}}
                            @if ($this->clients->hasMorePages())
                                <button wire:click="nextPage" class="join-item btn btn-sm hover:btn-primary">
                                    <span class="hidden sm:inline mr-1">Next</span>
                                    <x-icon name="phosphor.caret-right" class="w-3 h-3 md:w-4 md:h-4" />
                                </button>
                            @else
                                <button class="join-item btn btn-sm btn-disabled">
                                    <span class="hidden sm:inline mr-1">Next</span>
                                    <x-icon name="phosphor.caret-right" class="w-3 h-3 md:w-4 md:h-4" />
                                </button>
                            @endif
                        </div>
                    </div>

                    {{-- Mobile Page Info --}}
                    <div class="mt-4 text-center text-xs text-base-content/60 md:hidden">
                        {{ $this->paginationInfo['mobile'] }}
                    </div>
                </div>
            @else
                {{-- Show results info even when no pagination --}}
                @if ($this->clients->count() > 0)
                    <div class="mt-6 pt-4 border-t border-base-300">
                        <div
                            class="flex flex-col md:flex-row md:justify-between md:items-center space-y-4 md:space-y-0">
                            <div class="text-sm text-base-content/70 text-center md:text-left">
                                Menampilkan <span class="font-semibold">{{ $this->clients->count() }}</span> client
                                @if ($search)
                                    <br class="md:hidden">
                                    <span class="block md:inline mt-1 md:mt-0">
                                        untuk pencarian "<span class="font-semibold text-primary">{{ $search }}</span>"
                                    </span>
                                @endif
                            </div>

                            <div class="flex items-center justify-center md:justify-end gap-3">
                                <label class="text-sm text-base-content/70 font-medium">Per halaman:</label>
                                <x-select wire:model.live="perPage" :options="$this->perPageOptions" option-value="value"
                                    option-label="label" class="select-sm w-20" />
                            </div>
                        </div>
                    </div>
                @endif
            @endif
        </x-card>
    @endif

    {{-- FILTER DRAWER --}}
    <x-drawer wire:model="drawer" title="{{ \App\Class\Helper\FormatHelper::LABEL_FILTER }} Client" subtitle="Sesuaikan filter pencarian client" separator with-close-button
        close-on-escape class="w-11/12 lg:w-1/3">
        <div class="space-y-6">
            {{-- Status Filter --}}
            <div>
                <x-header title="Filter Status" subtitle="Filter berdasarkan status client" size="text-base" />
                <x-choices label="Status Client" :options="$this->statusFilterOptions" wire:model.live="statusFilter" option-value="id" option-label="name"
                    searchable single />
            </div>

            {{-- Sort Options --}}
            <div>
                <x-header title="Pengurutan" subtitle="Atur urutan tampilan client" size="text-base" />
                <x-choices label="Urutkan berdasarkan" :options="$this->sortOptions" wire:model.live="sortBy.column" option-value="id" option-label="name"
                    searchable single />
                <x-choices label="Arah pengurutan" :options="$this->sortDirectionOptions" wire:model.live="sortBy.direction" option-value="id" option-label="name"
                    searchable single />
            </div>

            {{-- Per Page --}}
            <div>
                <x-header title="Jumlah per Halaman" subtitle="Tentukan berapa client ditampilkan per halaman" size="text-base" />
                <x-choices label="{{ \App\Class\Helper\FormatHelper::LABEL_PER_PAGE }}" :options="$this->perPageOptions" wire:model.live="perPage" option-value="value" option-label="label"
                    searchable single />
            </div>

            {{-- Actions --}}
            <div class="flex gap-3">
                <x-button label="{{ \App\Class\Helper\FormatHelper::LABEL_RESET }} Filter" wire:click="clear" icon="{{ $this->clientUIConfig['icons']['close'] }}"
                    class="btn-ghost flex-1" />
                <x-button label="Tutup" @click="$wire.drawer = false" icon="{{ $this->clientUIConfig['icons']['close'] }}"
                    class="btn-primary flex-1" />
            </div>
        </div>
    </x-drawer>
</div>
