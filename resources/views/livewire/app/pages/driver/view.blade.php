{{-- View Sopir Page - Mary UI + DaisyUI Standards --}}
<div>
    {{-- HEADER --}}
    <x-header title="Detail {{ $user->name }}" icon="phosphor.eye-duotone" icon-classes="text-info h-10" separator
        progress-indicator>
        <x-slot:subtitle>
            <div>Lihat detail sopir <span class="text-info/60">{{ $user->name }}</span> dan informasi SIM di sini</div>
        </x-slot:subtitle>
        <x-slot:middle class="!justify-end">
            <div class="breadcrumbs text-sm hidden lg:block">
                <ul>
                    <li><a href="{{ route('app.driver.index') }}" wire:navigate>Data Sopir</a></li>
                    <li>Detail - {{ $user->name }}</li>
                </ul>
            </div>
        </x-slot:middle>
    </x-header>

    {{-- MAIN CONTENT --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- SOPIR PROFILE CARD --}}
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
                                    @if ($user->avatar)
                                        <img src="{{ $user->avatar }}" alt="{{ $user->name }}"
                                            class="w-full h-full object-cover" />
                                    @else
                                        <div
                                            class="w-full h-full bg-{{ $this->userRoleInfo['color'] }} text-{{ $this->userRoleInfo['color'] }}-content rounded-full flex items-center justify-center text-3xl font-bold">
                                            {{ $user->avatar_placeholder }}
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
                                    <x-icon name="phosphor.{{ $this->userStatusInfo['icon'] }}" class="h-4" />
                                    {{ $this->userStatusInfo['label'] }}
                                </div>

                                {{-- UPDATED: License Status Badge dengan icon dari helper --}}
                                @if ($user->driver)
                                    <div class="badge badge-{{ $this->licenseInfo['color'] }} badge-lg">
                                        <x-icon name="{{ $this->licenseInfo['icon'] }}" class="h-4" />
                                        {{ $this->licenseInfo['label'] }}
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- Driver Display Name --}}
                    @if ($user->driver && ($user->driver->vehicle_type || $user->driver->vehicle_plate))
                        <div class="bg-base-200 rounded-lg p-3">
                            <p class="text-sm font-medium text-base-content/70 mb-1">Nama Sopir dengan Kendaraan</p>
                            <p class="font-semibold text-base-content">{{ $user->driver->driver_display_name }}</p>
                        </div>
                    @endif

                    {{-- Quick Actions --}}
                    <div class="space-y-2 pt-4 border-t border-base-300">
                        <x-button label="Data Sopir" wire:click="backToList" class="btn-primary btn-outline btn-block"
                            icon="phosphor.truck" />

                        <x-button label="Edit {{ $user->name }}" wire:click="editDriver"
                            class="btn-warning btn-outline btn-block" icon="phosphor.pencil" />

                        <x-button label="Hapus {{ $user->name }}" wire:click="deleteUser"
                            class="btn-error btn-outline btn-block" icon="phosphor.trash" />

                        <x-button :label="$user->is_active ? 'Nonaktifkan' : 'Aktifkan'" wire:click="changeUserStatus"
                            class="btn-{{ $user->is_active ? 'warning' : 'success' }} btn-outline btn-block"
                            :icon="$user->is_active ? 'phosphor.pause' : 'phosphor.play'" />
                    </div>
                </div>
            </x-card>
        </div>

        {{-- MAIN CONTENT AREA --}}
        <div class="lg:col-span-2 space-y-6">
            {{-- Account Information --}}
            <x-card title="Informasi Akun {{ $user->name }}" separator class="shadow-md">
                <x-slot:menu>
                    <x-icon name="phosphor.user" class="h-5 text-info" />
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

                            {{-- UPDATED: Phone dengan icon dari DriverHelper --}}
                            @if ($user->driver && $user->driver->phone)
                                <div
                                    class="flex items-center gap-3 p-3 bg-base-200 rounded-lg hover:shadow-xl hover:shadow-primary transition-all duration-300">
                                    <x-icon name="{{ $this->driverUIConfig['icons']['phone'] }}"
                                        class="h-5 text-{{ $this->driverUIConfig['colors']['phone'] }} flex-shrink-0" />
                                    <div class="min-w-0 flex-1">
                                        <p class="text-sm font-medium text-base-content/70">Nomor Telepon</p>
                                        <p class="font-semibold">{{ $user->driver->formatted_phone }}</p>
                                    </div>
                                </div>
                            @endif

                            {{-- UPDATED: Address dengan icon dari DriverHelper --}}
                            @if ($user->driver && $user->driver->address)
                                <div
                                    class="flex items-start gap-3 p-3 bg-base-200 rounded-lg hover:shadow-xl hover:shadow-primary transition-all duration-300">
                                    <x-icon name="{{ $this->driverUIConfig['icons']['address'] }}"
                                        class="h-5 text-{{ $this->driverUIConfig['colors']['address'] }} flex-shrink-0 mt-0.5" />
                                    <div class="min-w-0 flex-1">
                                        <p class="text-sm font-medium text-base-content/70">Alamat</p>
                                        <p class="font-semibold">{{ $user->driver->address }}</p>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Account Status --}}
                    <div class="space-y-4">
                        <h4 class="font-semibold text-base-content border-b border-info pb-2">Status Akun</h4>

                        <div class="space-y-3">
                            {{-- DYNAMIC: Active Status --}}
                            <div
                                class="flex items-center gap-3 p-3 bg-base-200 rounded-lg hover:shadow-xl hover:shadow-primary transition-all duration-300">
                                <x-icon name="phosphor.{{ $this->userStatusInfo['icon'] }}"
                                    class="h-5 text-{{ $this->userStatusInfo['color'] }} flex-shrink-0" />
                                <div class="min-w-0 flex-1">
                                    <p class="text-sm font-medium text-base-content/70">Status Akun</p>
                                    <p class="font-semibold">{{ $this->userStatusInfo['label'] }}</p>
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
                                    <p class="text-xs text-base-content/50">{{ $this->userRoleInfo['description'] }}</p>
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

            {{-- License Information --}}
            @if ($user->driver)
                <x-card title="Informasi SIM" separator class="shadow-md">
                    <x-slot:menu>
                        <x-icon name="phosphor.identification-card" class="h-5 text-info" />
                    </x-slot:menu>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        {{-- License Details --}}
                        <div class="space-y-4">
                            <h4 class="font-semibold text-base-content border-b border-info pb-2">Detail SIM</h4>

                            <div class="space-y-3">
                                {{-- UPDATED: License Type dengan icon dari DriverHelper --}}
                                <div
                                    class="flex items-center gap-3 p-3 bg-base-200 rounded-lg hover:shadow-xl hover:shadow-primary transition-all duration-300">
                                    <x-icon name="{{ $this->driverUIConfig['icons']['license_type'] }}"
                                        class="h-5 text-{{ $user->driver->license_color }} flex-shrink-0" />
                                    <div class="min-w-0 flex-1">
                                        <p class="text-sm font-medium text-base-content/70">Jenis SIM</p>
                                        <p class="font-semibold">{{ $user->driver->license_label }}</p>
                                        <p class="text-xs text-base-content/50">
                                            {{ $this->licenseInfo['statusMessage'] }}
                                        </p>
                                    </div>
                                </div>

                                {{-- UPDATED: License Number dengan icon dari DriverHelper --}}
                                <div
                                    class="flex items-center gap-3 p-3 bg-base-200 rounded-lg hover:shadow-xl hover:shadow-primary transition-all duration-300">
                                    <x-icon name="{{ $this->driverUIConfig['icons']['license_number'] }}"
                                        class="h-5 text-{{ $this->driverUIConfig['colors']['license_number'] }} flex-shrink-0" />
                                    <div class="min-w-0 flex-1">
                                        <p class="text-sm font-medium text-base-content/70">Nomor SIM</p>
                                        <p class="font-semibold font-mono">
                                            {{ $user->driver->formatted_license_number }}</p>
                                        <p class="text-xs text-base-content/50">Original:
                                            {{ $user->driver->license_number }}</p>
                                    </div>
                                </div>

                                {{-- UPDATED: License Expiry dengan icon dari DriverHelper --}}
                                <div
                                    class="flex items-center gap-3 p-3 bg-base-200 rounded-lg hover:shadow-xl hover:shadow-primary transition-all duration-300">
                                    <x-icon name="{{ $this->driverUIConfig['icons']['license_expiry'] }}"
                                        class="h-5 text-{{ $this->driverUIConfig['colors']['license_expiry'] }} flex-shrink-0" />
                                    <div class="min-w-0 flex-1">
                                        <p class="text-sm font-medium text-base-content/70">Tanggal Kadaluarsa</p>
                                        <p class="font-semibold">{{ $user->driver->formatted_license_expiry }}</p>
                                        <p class="text-xs text-base-content/50">
                                            {{ $user->driver->license_expiry->diffForHumans() }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- License Status --}}
                        <div class="space-y-4">
                            <h4 class="font-semibold text-base-content border-b border-info pb-2">Status SIM</h4>

                            <div class="space-y-3">
                                {{-- UPDATED: License Status dengan icon langsung dari licenseInfo --}}
                                <div
                                    class="flex items-center gap-3 p-3 bg-base-200 rounded-lg hover:shadow-xl hover:shadow-primary transition-all duration-300">
                                    <x-icon name="{{ $this->licenseInfo['icon'] }}"
                                        class="h-5 text-{{ $this->licenseInfo['color'] }} flex-shrink-0" />
                                    <div class="min-w-0 flex-1">
                                        <p class="text-sm font-medium text-base-content/70">Status SIM</p>
                                        <p class="font-semibold">{{ $this->licenseInfo['label'] }}</p>
                                    </div>
                                </div>

                                {{-- Days to Expiry --}}
                                @if (!$this->licenseInfo['isExpired'])
                                    <div
                                        class="flex items-center gap-3 p-3 bg-base-200 rounded-lg hover:shadow-xl hover:shadow-primary transition-all duration-300">
                                        <x-icon name="phosphor.clock" class="h-5 text-accent flex-shrink-0" />
                                        <div class="min-w-0 flex-1">
                                            <p class="text-sm font-medium text-base-content/70">Sisa Waktu</p>
                                            <p class="font-semibold">
                                                {{ $this->licenseInfo['daysToExpiry'] }} hari lagi
                                            </p>
                                            @if ($this->licenseInfo['showWarning'])
                                                <p class="text-xs text-warning mt-1">
                                                    <x-icon name="phosphor.warning" class="h-4 inline" />
                                                    Masa berlaku SIM dalam periode warning (90 hari)
                                                </p>
                                            @endif
                                        </div>
                                    </div>
                                @else
                                    <div
                                        class="flex items-center gap-3 p-3 bg-base-200 rounded-lg hover:shadow-xl hover:shadow-primary transition-all duration-300">
                                        <x-icon name="phosphor.x-circle" class="h-5 text-error flex-shrink-0" />
                                        <div class="min-w-0 flex-1">
                                            <p class="text-sm font-medium text-base-content/70">Telah Kadaluarsa</p>
                                            <p class="font-semibold text-error">
                                                {{ $this->licenseInfo['daysToExpiry'] }} hari yang lalu
                                            </p>
                                        </div>
                                    </div>
                                @endif

                                {{-- License Valid Status --}}
                                <div
                                    class="flex items-center gap-3 p-3 bg-base-200 rounded-lg hover:shadow-xl hover:shadow-primary transition-all duration-300">
                                    <x-icon
                                        name="phosphor.{{ $user->driver->hasValidLicense() ? 'shield-check' : 'warning-octagon' }}"
                                        class="h-5 text-{{ $user->driver->hasValidLicense() ? 'success' : 'error' }} flex-shrink-0" />
                                    <div class="min-w-0 flex-1">
                                        <p class="text-sm font-medium text-base-content/70">Kelayakan SIM</p>
                                        <p class="font-semibold">
                                            {{ $user->driver->hasValidLicense() ? 'Layak Berkendara' : 'Tidak Layak Berkendara' }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </x-card>
            @endif

            {{-- Vehicle Information --}}
            @if ($this->vehicleInfo['hasVehicle'])
                <x-card title="Informasi Kendaraan" separator class="shadow-md">
                    <x-slot:menu>
                        <x-icon name="phosphor.truck-trailer" class="h-5 text-info" />
                    </x-slot:menu>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        {{-- Vehicle Details --}}
                        <div class="space-y-4">
                            <h4 class="font-semibold text-base-content border-b border-info pb-2">Detail Kendaraan</h4>

                            <div class="space-y-3">
                                {{-- UPDATED: Vehicle Type dengan icon dari DriverHelper --}}
                                @if ($this->vehicleInfo['hasType'])
                                    <div
                                        class="flex items-center gap-3 p-3 bg-base-200 rounded-lg hover:shadow-xl hover:shadow-primary transition-all duration-300">
                                        <x-icon name="{{ $this->driverUIConfig['icons']['vehicle_type'] }}"
                                            class="h-5 text-{{ $this->driverUIConfig['colors']['vehicle_type'] }} flex-shrink-0" />
                                        <div class="min-w-0 flex-1">
                                            <p class="text-sm font-medium text-base-content/70">Jenis Kendaraan</p>
                                            <p class="font-semibold">{{ $user->driver->vehicle_type }}</p>
                                            <p class="text-xs text-base-content/50">{{ $this->vehicleInfo['description'] }}</p>
                                        </div>
                                    </div>
                                @endif

                                {{-- UPDATED: Vehicle Plate dengan icon dari DriverHelper --}}
                                @if ($this->vehicleInfo['hasPlate'])
                                    <div
                                        class="flex items-center gap-3 p-3 bg-base-200 rounded-lg hover:shadow-xl hover:shadow-primary transition-all duration-300">
                                        <x-icon name="{{ $this->driverUIConfig['icons']['vehicle_plate'] }}"
                                            class="h-5 text-{{ $this->driverUIConfig['colors']['vehicle_plate'] }} flex-shrink-0" />
                                        <div class="min-w-0 flex-1">
                                            <p class="text-sm font-medium text-base-content/70">Plat Nomor</p>
                                            <p class="font-semibold font-mono">
                                                {{ $user->driver->formatted_vehicle_plate }}</p>
                                            <p class="text-xs text-base-content/50">Original:
                                                {{ $user->driver->vehicle_plate }}</p>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>

                        {{-- Vehicle Status --}}
                        <div class="space-y-4">
                            <h4 class="font-semibold text-base-content border-b border-info pb-2">Status Kendaraan</h4>

                            <div class="space-y-3">
                                {{-- UPDATED: Vehicle Status dengan icon dari vehicleInfo --}}
                                <div
                                    class="flex items-center gap-3 p-3 bg-base-200 rounded-lg hover:shadow-xl hover:shadow-primary transition-all duration-300">
                                    <x-icon name="{{ $this->vehicleInfo['icon'] }}"
                                        class="h-5 text-{{ $this->driverUIConfig['colors']['vehicle_status'] }} flex-shrink-0" />
                                    <div class="min-w-0 flex-1">
                                        <p class="text-sm font-medium text-base-content/70">Status Kendaraan</p>
                                        <p class="font-semibold">{{ $this->vehicleInfo['status'] }}</p>
                                        <p class="text-xs text-base-content/50">{{ $this->vehicleInfo['description'] }}</p>
                                    </div>
                                </div>

                                {{-- UPDATED: Vehicle Display Name dengan icon dari DriverHelper --}}
                                <div
                                    class="flex items-center gap-3 p-3 bg-base-200 rounded-lg hover:shadow-xl hover:shadow-primary transition-all duration-300">
                                    <x-icon name="{{ $this->driverUIConfig['icons']['driver_display'] }}"
                                        class="h-5 text-{{ $this->driverUIConfig['colors']['driver_display'] }} flex-shrink-0" />
                                    <div class="min-w-0 flex-1">
                                        <p class="text-sm font-medium text-base-content/70">Identitas Lengkap</p>
                                        <p class="font-semibold">{{ $user->driver->driver_display_name }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </x-card>
            @else
                {{-- No Vehicle Info Card --}}
                <x-card title="Informasi Kendaraan" separator class="shadow-md">
                    <x-slot:menu>
                        <x-icon name="phosphor.truck-trailer" class="h-5 text-info" />
                    </x-slot:menu>

                    <div class="text-center py-8">
                        <x-icon name="phosphor.truck-trailer" class="w-16 h-16 mx-auto text-base-content/30 mb-4" />
                        <h3 class="text-lg font-semibold text-base-content/60 mb-2">{{ $this->vehicleInfo['status'] }}</h3>
                        <p class="text-base-content/40 mb-6">{{ $this->vehicleInfo['description'] }}</p>
                        <x-button label="Edit Sopir" wire:click="editDriver" icon="phosphor.pencil" class="btn-warning" />
                    </div>
                </x-card>
            @endif

            {{-- Activity Summary --}}
            <x-card title="Ringkasan Aktivitas Akun" separator class="shadow-md">
                <x-slot:menu>
                    <x-icon name="phosphor.person-simple-run" class="h-5 text-info" />
                </x-slot:menu>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    {{-- Account Age --}}
                    <x-stat title="Usia Akun" :value="$this->userActivity['joinedDays']"
                        description="{{ $this->userActivity['accountAge'] }}"
                        icon="phosphor.calendar-check" color="text-primary"
                        class="bg-base-200 hover:shadow-xl hover:shadow-primary transition-all duration-300" />

                    {{-- Last Activity --}}
                    <x-stat title="Aktivitas Terakhir" :value="$this->userActivity['lastUpdateDays']"
                        description="{{ $this->userActivity['lastUpdate'] }}" icon="phosphor.clock-clockwise"
                        color="text-secondary"
                        class="bg-base-200 hover:shadow-xl hover:shadow-primary transition-all duration-300" />

                    {{-- Email Status --}}
                    <x-stat title="Status Email"
                        :value="$this->userActivity['isEmailVerified'] ? 'Terverifikasi' : 'Belum Verifikasi'"
                        description="{{ $this->userActivity['isEmailVerified'] ? 'Email sudah dikonfirmasi' : 'Perlu verifikasi email' }}"
                        icon="phosphor.{{ $this->userActivity['isEmailVerified'] ? 'check-circle' : 'warning-circle' }}"
                        color="text-{{ $this->userActivity['isEmailVerified'] ? 'success' : 'warning' }}"
                        class="bg-base-200 hover:shadow-xl hover:shadow-primary transition-all duration-300" />
                </div>

                {{-- Additional Activity Info --}}
                <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-4">
                    {{-- Profile Completeness --}}
                    <div class="bg-base-200 rounded-lg p-4">
                        <div class="flex items-center justify-between mb-2">
                            <h5 class="font-semibold text-base-content">Kelengkapan Profil</h5>
                            <span class="text-sm font-medium">{{ $this->profileCompleteness['percentage'] }}%</span>
                        </div>
                        <div class="w-full bg-base-300 rounded-full h-2">
                            <div class="bg-primary h-2 rounded-full transition-all duration-300"
                                style="width: {{ $this->profileCompleteness['percentage'] }}%"></div>
                        </div>
                        <p class="text-xs text-base-content/60 mt-2">
                            {{ $this->profileCompleteness['description'] }}
                        </p>
                    </div>

                    {{-- Account Health --}}
                    <div class="bg-base-200 rounded-lg p-4">
                        <div class="flex items-center justify-between mb-2">
                            <h5 class="font-semibold text-base-content">Kesehatan Akun</h5>
                            <div class="badge badge-{{ $this->accountHealth['color'] }} badge-sm">
                                {{ $this->accountHealth['label'] }}
                            </div>
                        </div>
                        <p class="text-xs text-base-content/60">
                            {{ $this->accountHealth['description'] }}
                        </p>
                    </div>
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
