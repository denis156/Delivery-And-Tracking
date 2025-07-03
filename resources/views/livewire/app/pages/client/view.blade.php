{{-- View Client Page - Mary UI + DaisyUI Standards --}}
<div>
    {{-- HEADER --}}
    <x-header title="Detail {{ $client->company_name }}" icon="{{ $this->clientUIConfig['icons']['view'] }}" icon-classes="text-info h-10" separator
        progress-indicator>
        <x-slot:subtitle>
            <div>Lihat detail client <span class="text-info/60">{{ $client->company_name }}</span> dan informasi perusahaan di sini</div>
        </x-slot:subtitle>
        <x-slot:middle class="!justify-end">
            <div class="breadcrumbs text-sm hidden lg:block">
                <ul>
                    <li><a href="{{ route('app.client.index') }}" wire:navigate>{{ \App\Class\Helper\ClientHelper::PAGE_TITLE_INDEX }}</a></li>
                    <li>Detail - {{ $client->company_name }}</li>
                </ul>
            </div>
        </x-slot:middle>
    </x-header>

    {{-- MAIN CONTENT --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- CLIENT PROFILE CARD --}}
        <div class="lg:col-span-1">
            <x-card title="Profil {{ $user->name }}" separator sticky class="shadow-md">
                <x-slot:menu>
                    <x-icon name="{{ $this->userRoleInfo['icon'] }}" class="h-5 text-info" />
                </x-slot:menu>

                <div class="space-y-6">
                    {{-- Avatar & Basic Info --}}
                    <div class="text-center space-y-4">
                        {{-- Avatar --}}
                        <div class="flex justify-center">
                            <div class="avatar">
                                <div
                                    class="w-32 h-32 rounded-full ring ring-{{ $this->userStatusInfo['color'] }} ring-offset-base-100 ring-offset-4 hover:shadow-xl hover:shadow-primary transition-all duration-300">
                                    @if ($user->avatar_url)
                                        <img src="{{ Storage::url($user->avatar_url) }}" alt="{{ $user->name }}"
                                            class="w-full h-full object-cover" />
                                    @else
                                        <div
                                            class="w-full h-full bg-{{ $this->userRoleInfo['color'] }} text-{{ $this->userRoleInfo['color'] }}-content rounded-full flex items-center justify-center text-3xl font-bold">
                                            {{ $this->avatarPlaceholder }}
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- Name & Email --}}
                        <div>
                            <h3 class="text-xl font-bold text-base-content">{{ $user->name }}</h3>
                            <p class="text-base-content/70 break-all">{{ $user->email }}</p>

                            {{-- DYNAMIC: Status & Role Badges menggunakan helper --}}
                            <div class="flex flex-wrap justify-center gap-2 mt-3">
                                {{-- Role Badge --}}
                                <div class="badge badge-{{ $this->userRoleInfo['color'] }} badge-lg">
                                    <x-icon name="{{ $this->userRoleInfo['icon'] }}" class="h-4" />
                                    {{ $this->userRoleInfo['label'] }}
                                </div>

                                {{-- Status Badge --}}
                                <div class="badge badge-{{ $this->userStatusInfo['color'] }} badge-lg">
                                    <x-icon name="{{ $this->userStatusInfo['icon'] }}" class="h-4" />
                                    {{ $this->userStatusInfo['label'] }}
                                </div>

                                {{-- Company Code Badge --}}
                                @if ($client->company_code)
                                    <div class="badge badge-{{ $this->clientUIConfig['colors']['company_code'] }} badge-lg">
                                        <x-icon name="{{ $this->clientUIConfig['icons']['company_code'] }}" class="h-4" />
                                        {{ $client->company_code }}
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- Company Display Name --}}
                    @if ($this->companyDisplayInfo['display_name'])
                        <div class="bg-base-200 rounded-lg p-3">
                            <p class="text-sm font-medium text-base-content/70 mb-1">Nama Display Perusahaan</p>
                            <p class="font-semibold text-base-content">{{ $this->companyDisplayInfo['display_name'] }}</p>
                        </div>
                    @endif

                    {{-- Quick Actions --}}
                    <div class="space-y-2 pt-4 border-t border-base-300">
                        <x-button label="Data Client" wire:click="backToIndex" class="btn-primary btn-outline btn-block"
                            icon="{{ $this->clientUIConfig['icons']['back'] }}" />

                        <x-button label="Edit {{ $client->company_name }}" wire:click="editClient"
                            class="btn-warning btn-outline btn-block" icon="{{ $this->clientUIConfig['icons']['edit'] }}" />

                        <x-button label="Hapus {{ $client->company_name }}" wire:click="openDeleteModal"
                            class="btn-error btn-outline btn-block" icon="{{ $this->clientUIConfig['icons']['delete'] }}" />

                        <x-button :label="$user->is_active ? 'Nonaktifkan' : 'Aktifkan'" wire:click="openChangeStatusModal"
                            class="btn-{{ $user->is_active ? 'warning' : 'success' }} btn-outline btn-block"
                            :icon="$user->is_active ? $this->clientUIConfig['icons']['status'] : $this->clientUIConfig['icons']['status']" />
                    </div>
                </div>
            </x-card>
        </div>

        {{-- MAIN CONTENT AREA --}}
        <div class="lg:col-span-2 space-y-6">
            {{-- Account Information --}}
            <x-card title="Informasi Akun {{ $user->name }}" separator class="shadow-md">
                <x-slot:menu>
                    <x-icon name="{{ $this->userRoleInfo['icon'] }}" class="h-5 text-info" />
                </x-slot:menu>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    {{-- Personal Details --}}
                    <div class="space-y-4">
                        <h4 class="font-semibold text-base-content border-b border-info pb-2">Detail Pribadi</h4>

                        <div class="space-y-3">
                            {{-- Name --}}
                            <div
                                class="flex items-center gap-3 p-3 bg-base-200 rounded-lg hover:shadow-xl hover:shadow-primary transition-all duration-300">
                                <x-icon name="phosphor.user" class="h-5 text-primary flex-shrink-0" />
                                <div class="min-w-0 flex-1">
                                    <p class="text-sm font-medium text-base-content/70">Nama Lengkap</p>
                                    <p class="font-semibold truncate">{{ $user->name }}</p>
                                </div>
                            </div>

                            {{-- Email --}}
                            <div
                                class="flex items-center gap-3 p-3 bg-base-200 rounded-lg hover:shadow-xl hover:shadow-primary transition-all duration-300">
                                <x-icon name="phosphor.envelope" class="h-5 text-info flex-shrink-0" />
                                <div class="min-w-0 flex-1">
                                    <p class="text-sm font-medium text-base-content/70">Email</p>
                                    <p class="font-semibold break-all">{{ $user->email }}</p>
                                </div>
                            </div>

                            {{-- DYNAMIC: Role --}}
                            <div
                                class="flex items-center gap-3 p-3 bg-base-200 rounded-lg hover:shadow-xl hover:shadow-primary transition-all duration-300">
                                <x-icon name="{{ $this->userRoleInfo['icon'] }}"
                                    class="h-5 text-{{ $this->userRoleInfo['color'] }} flex-shrink-0" />
                                <div class="min-w-0 flex-1">
                                    <p class="text-sm font-medium text-base-content/70">Peran</p>
                                    <p class="font-semibold">{{ $this->userRoleInfo['label'] }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Account Status --}}
                    <div class="space-y-4">
                        <h4 class="font-semibold text-base-content border-b border-info pb-2">Status Akun</h4>

                        <div class="space-y-3">
                            {{-- DYNAMIC: Active Status --}}
                            <div
                                class="flex items-center gap-3 p-3 bg-base-200 rounded-lg hover:shadow-xl hover:shadow-primary transition-all duration-300">
                                <x-icon name="{{ $this->userStatusInfo['icon'] }}"
                                    class="h-5 text-{{ $this->userStatusInfo['color'] }} flex-shrink-0" />
                                <div class="min-w-0 flex-1">
                                    <p class="text-sm font-medium text-base-content/70">Status Akun</p>
                                    <p class="font-semibold">{{ $this->userStatusInfo['label'] }}</p>
                                </div>
                            </div>

                            {{-- Email Verification --}}
                            <div
                                class="flex items-center gap-3 p-3 bg-base-200 rounded-lg hover:shadow-xl hover:shadow-primary transition-all duration-300">
                                <x-icon
                                    name="phosphor.{{ $user->email_verified_at ? 'shield-check' : 'shield-warning' }}"
                                    class="h-5 text-{{ $user->email_verified_at ? 'success' : 'warning' }} flex-shrink-0" />
                                <div class="min-w-0 flex-1">
                                    <p class="text-sm font-medium text-base-content/70">Verifikasi Email</p>
                                    <p class="font-semibold">
                                        {{ $user->email_verified_at ? 'Terverifikasi' : 'Belum Terverifikasi' }}
                                    </p>
                                    @if ($user->email_verified_at)
                                        <p class="text-xs text-base-content/50">
                                            {{ $user->email_verified_at->format('d M Y H:i') }}
                                        </p>
                                    @endif
                                </div>
                            </div>

                            {{-- Join Date --}}
                            <div
                                class="flex items-center gap-3 p-3 bg-base-200 rounded-lg hover:shadow-xl hover:shadow-primary transition-all duration-300">
                                <x-icon name="phosphor.calendar-plus" class="h-5 text-secondary flex-shrink-0" />
                                <div class="min-w-0 flex-1">
                                    <p class="text-sm font-medium text-base-content/70">Bergabung</p>
                                    <p class="font-semibold">{{ $user->created_at->format('d M Y') }}</p>
                                    <p class="text-xs text-base-content/50">
                                        {{ $user->created_at->diffForHumans() }}
                                    </p>
                                </div>
                            </div>

                            {{-- Last Update --}}
                            <div
                                class="flex items-center gap-3 p-3 bg-base-200 rounded-lg hover:shadow-xl hover:shadow-primary transition-all duration-300">
                                <x-icon name="phosphor.clock-clockwise" class="h-5 text-accent flex-shrink-0" />
                                <div class="min-w-0 flex-1">
                                    <p class="text-sm font-medium text-base-content/70">Terakhir Diperbarui</p>
                                    <p class="font-semibold">{{ $user->updated_at->format('d M Y') }}</p>
                                    <p class="text-xs text-base-content/50">
                                        {{ $user->updated_at->diffForHumans() }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </x-card>

            {{-- Company Information --}}
            <x-card title="Informasi Perusahaan" separator class="shadow-md">
                <x-slot:menu>
                    <x-icon name="{{ $this->clientUIConfig['icons']['company_name'] }}" class="h-5 text-info" />
                </x-slot:menu>

                <div class="space-y-3">
                    {{-- Company Name --}}
                    <div
                        class="flex items-center gap-3 p-3 bg-base-200 rounded-lg hover:shadow-xl hover:shadow-primary transition-all duration-300">
                        <x-icon name="{{ $this->clientUIConfig['icons']['company_name'] }}"
                            class="h-5 text-{{ $this->clientUIConfig['colors']['company_name'] }} flex-shrink-0" />
                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-medium text-base-content/70">Nama Perusahaan</p>
                            <p class="font-semibold">{{ $this->companyDisplayInfo['name'] }}</p>
                        </div>
                    </div>

                    {{-- Company Code --}}
                    <div
                        class="flex items-center gap-3 p-3 bg-base-200 rounded-lg hover:shadow-xl hover:shadow-primary transition-all duration-300">
                        <x-icon name="{{ $this->clientUIConfig['icons']['company_code'] }}"
                            class="h-5 text-{{ $this->clientUIConfig['colors']['company_code'] }} flex-shrink-0" />
                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-medium text-base-content/70">Kode Perusahaan</p>
                            <p class="font-semibold font-mono">{{ $this->companyDisplayInfo['code'] }}</p>
                        </div>
                    </div>

                    {{-- Phone --}}
                    <div
                        class="flex items-center gap-3 p-3 bg-base-200 rounded-lg hover:shadow-xl hover:shadow-primary transition-all duration-300">
                        <x-icon name="{{ $this->clientUIConfig['icons']['phone'] }}"
                            class="h-5 text-{{ $this->clientUIConfig['colors']['phone'] }} flex-shrink-0" />
                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-medium text-base-content/70">Telepon Perusahaan</p>
                            <p class="font-semibold">{{ $this->companyDetails['phone'] }}</p>
                        </div>
                    </div>

                    {{-- Fax --}}
                    @if ($this->companyDetails['fax'] !== \App\Class\Helper\ClientHelper::DEFAULT_EMPTY_VALUE)
                        <div
                            class="flex items-center gap-3 p-3 bg-base-200 rounded-lg hover:shadow-xl hover:shadow-primary transition-all duration-300">
                            <x-icon name="{{ $this->clientUIConfig['icons']['fax'] }}"
                                class="h-5 text-{{ $this->clientUIConfig['colors']['fax'] }} flex-shrink-0" />
                            <div class="min-w-0 flex-1">
                                <p class="text-sm font-medium text-base-content/70">Fax Perusahaan</p>
                                <p class="font-semibold">{{ $this->companyDetails['fax'] }}</p>
                            </div>
                        </div>
                    @endif

                    {{-- Tax ID --}}
                    @if ($this->companyDetails['tax_id'] !== \App\Class\Helper\ClientHelper::DEFAULT_EMPTY_VALUE)
                        <div
                            class="flex items-center gap-3 p-3 bg-base-200 rounded-lg hover:shadow-xl hover:shadow-primary transition-all duration-300">
                            <x-icon name="{{ $this->clientUIConfig['icons']['tax_id'] }}"
                                class="h-5 text-{{ $this->clientUIConfig['colors']['tax_id'] }} flex-shrink-0" />
                            <div class="min-w-0 flex-1">
                                <p class="text-sm font-medium text-base-content/70">NPWP</p>
                                <p class="font-semibold font-mono">{{ $this->companyDetails['tax_id'] }}</p>
                            </div>
                        </div>
                    @endif
                </div>

                {{-- Additional Company Info --}}
                <div class="mt-6 pt-4 border-t border-base-300 space-y-3">
                    {{-- Company Display Name --}}
                    <div
                        class="flex items-center gap-3 p-3 bg-base-200 rounded-lg hover:shadow-xl hover:shadow-primary transition-all duration-300">
                        <x-icon name="{{ $this->clientUIConfig['icons']['company_name'] }}"
                            class="h-5 text-primary flex-shrink-0" />
                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-medium text-base-content/70">Identitas Lengkap</p>
                            <p class="font-semibold">{{ $this->companyDisplayInfo['display_name'] }}</p>
                        </div>
                    </div>

                    {{-- Company Address --}}
                    <div
                        class="flex items-start gap-3 p-3 bg-base-200 rounded-lg hover:shadow-xl hover:shadow-primary transition-all duration-300">
                        <x-icon name="{{ $this->clientUIConfig['icons']['company_address'] }}"
                            class="h-5 text-{{ $this->clientUIConfig['colors']['company_address'] }} flex-shrink-0 mt-0.5" />
                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-medium text-base-content/70">Alamat Perusahaan</p>
                            <p class="font-semibold">{{ $this->companyDisplayInfo['address'] }}</p>
                        </div>
                    </div>
                </div>
            </x-card>

            {{-- Contact Information --}}
            <x-card title="Informasi Contact Person" separator class="shadow-md">
                <x-slot:menu>
                    <x-icon name="{{ $this->clientUIConfig['icons']['contact_person'] }}" class="h-5 text-info" />
                </x-slot:menu>

                <div class="space-y-3">
                    {{-- Contact Person --}}
                    <div
                        class="flex items-center gap-3 p-3 bg-base-200 rounded-lg hover:shadow-xl hover:shadow-primary transition-all duration-300">
                        <x-icon name="{{ $this->clientUIConfig['icons']['contact_person'] }}"
                            class="h-5 text-{{ $this->clientUIConfig['colors']['contact_person'] }} flex-shrink-0" />
                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-medium text-base-content/70">Nama Contact Person</p>
                            <p class="font-semibold">{{ $this->contactInfo['person'] }}</p>
                        </div>
                    </div>

                    {{-- Contact Position --}}
                    @if ($this->contactInfo['position'] !== \App\Class\Helper\ClientHelper::DEFAULT_EMPTY_VALUE)
                        <div
                            class="flex items-center gap-3 p-3 bg-base-200 rounded-lg hover:shadow-xl hover:shadow-primary transition-all duration-300">
                            <x-icon name="{{ $this->clientUIConfig['icons']['contact_position'] }}"
                                class="h-5 text-{{ $this->clientUIConfig['colors']['contact_position'] }} flex-shrink-0" />
                            <div class="min-w-0 flex-1">
                                <p class="text-sm font-medium text-base-content/70">Jabatan</p>
                                <p class="font-semibold">{{ $this->contactInfo['position'] }}</p>
                            </div>
                        </div>
                    @endif

                    {{-- Contact Phone --}}
                    <div
                        class="flex items-center gap-3 p-3 bg-base-200 rounded-lg hover:shadow-xl hover:shadow-primary transition-all duration-300">
                        <x-icon name="{{ $this->clientUIConfig['icons']['contact_phone'] }}"
                            class="h-5 text-{{ $this->clientUIConfig['colors']['contact_phone'] }} flex-shrink-0" />
                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-medium text-base-content/70">Telepon Contact</p>
                            <p class="font-semibold">{{ $this->contactInfo['phone'] }}</p>
                        </div>
                    </div>

                    {{-- Contact Email --}}
                    <div
                        class="flex items-center gap-3 p-3 bg-base-200 rounded-lg hover:shadow-xl hover:shadow-primary transition-all duration-300">
                        <x-icon name="{{ $this->clientUIConfig['icons']['contact_email'] }}"
                            class="h-5 text-{{ $this->clientUIConfig['colors']['contact_email'] }} flex-shrink-0" />
                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-medium text-base-content/70">Email Contact</p>
                            <p class="font-semibold break-all">{{ $this->contactInfo['email'] }}</p>
                        </div>
                    </div>
                </div>
            </x-card>

            {{-- Location Information --}}
            <x-card title="Informasi Lokasi" separator class="shadow-md">
                <x-slot:menu>
                    <x-icon name="{{ $this->clientUIConfig['icons']['coordinates'] }}" class="h-5 text-info" />
                </x-slot:menu>

                @if ($this->locationInfo['has_coordinates'])
                    <div class="space-y-4">
                        {{-- Coordinates --}}
                        <div
                            class="flex items-center gap-3 p-3 bg-base-200 rounded-lg hover:shadow-xl hover:shadow-primary transition-all duration-300">
                            <x-icon name="{{ $this->clientUIConfig['icons']['coordinates'] }}"
                                class="h-5 text-{{ $this->clientUIConfig['colors']['coordinates'] }} flex-shrink-0" />
                            <div class="min-w-0 flex-1">
                                <p class="text-sm font-medium text-base-content/70">Koordinat GPS</p>
                                <p class="font-semibold font-mono">{{ $this->locationInfo['coordinates'] }}</p>
                            </div>
                        </div>

                        {{-- Validation Status --}}
                        <div
                            class="flex items-center gap-3 p-3 bg-base-200 rounded-lg hover:shadow-xl hover:shadow-primary transition-all duration-300">
                            <x-icon name="phosphor.{{ $this->locationInfo['is_valid'] ? 'check-circle' : 'warning-circle' }}"
                                class="h-5 text-{{ $this->locationInfo['is_valid'] ? 'success' : 'warning' }} flex-shrink-0" />
                            <div class="min-w-0 flex-1">
                                <p class="text-sm font-medium text-base-content/70">Status Validasi</p>
                                <p class="font-semibold">
                                    {{ $this->locationInfo['is_valid'] ? 'Koordinat valid untuk Indonesia' : 'Koordinat mungkin tidak akurat' }}
                                </p>
                            </div>
                        </div>

                        {{-- Map Actions --}}
                        <div class="flex flex-col sm:flex-row gap-3 mt-4">
                            <x-button label="Lihat di Peta Internal" icon="{{ $this->clientUIConfig['icons']['map'] }}"
                                wire:click="openMapModal" class="btn-info flex-1" />

                            <x-button label="Buka Google Maps" icon="phosphor.globe"
                                link="{{ $this->locationInfo['map_url'] }}" external
                                class="btn-success flex-1" />
                        </div>
                    </div>
                @else
                    {{-- No Coordinates Info --}}
                    <div class="text-center py-8">
                        <x-icon name="{{ $this->clientUIConfig['icons']['coordinates'] }}" class="w-16 h-16 mx-auto text-base-content/30 mb-4" />
                        <h3 class="text-lg font-semibold text-base-content/60 mb-2">Koordinat GPS Belum Diatur</h3>
                        <p class="text-base-content/40 mb-6">Lokasi perusahaan belum memiliki koordinat GPS yang valid</p>
                        <x-button label="Edit Client" wire:click="editClient" icon="phosphor.pencil" class="btn-warning" />
                    </div>
                @endif
            </x-card>

            {{-- Activity Summary --}}
            <x-card title="Ringkasan Aktivitas Client" separator class="shadow-md">
                <x-slot:menu>
                    <x-icon name="phosphor.chart-line" class="h-5 text-info" />
                </x-slot:menu>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    {{-- Account Age --}}
                    <x-stat title="Usia Akun" :value="$this->userActivity['created_relative']"
                        description="Bergabung {{ $this->userActivity['created_at'] }}"
                        icon="phosphor.calendar-check" color="text-primary"
                        class="bg-base-200 hover:shadow-xl hover:shadow-primary transition-all duration-300" />

                    {{-- Last Activity --}}
                    <x-stat title="Aktivitas Terakhir" :value="$this->userActivity['updated_relative']"
                        description="Diperbarui {{ $this->userActivity['updated_at'] }}" icon="phosphor.clock-clockwise"
                        color="text-secondary"
                        class="bg-base-200 hover:shadow-xl hover:shadow-primary transition-all duration-300" />

                    {{-- Delivery Orders --}}
                    <x-stat title="Surat Jalan" :value="$this->clientMetrics['delivery_orders_count']"
                        description="{{ $this->clientMetrics['delivery_orders_label'] }}" icon="phosphor.truck"
                        color="text-accent"
                        class="bg-base-200 hover:shadow-xl hover:shadow-primary transition-all duration-300" />
                </div>
            </x-card>
        </div>
    </div>

    {{-- MODAL COMPONENTS --}}
    {{-- Change Status Modal --}}
    <livewire:app.component.user.change-status-modal />

    {{-- Delete User Modal --}}
    <livewire:app.component.user.delete-user-modal />
</div>
