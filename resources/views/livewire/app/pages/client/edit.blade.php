{{-- Edit Client Page - Mary UI + DaisyUI Standards --}}
<div>
    {{-- HEADER --}}
    <x-header title="Edit {{ $client->company_name }}" icon="{{ $this->clientUIConfig['icons']['edit'] }}" icon-classes="text-warning h-10" separator
        progress-indicator>
        <x-slot:subtitle>
            <div>Edit informasi client <span class="text-warning/60">{{ $client->company_name }}</span> dan data perusahaan</div>
        </x-slot:subtitle>
        <x-slot:middle class="!justify-end">
            <div class="breadcrumbs text-sm hidden lg:block">
                <ul>
                    <li><a href="{{ route('app.client.index') }}" wire:navigate>{{ \App\Class\Helper\ClientHelper::PAGE_TITLE_INDEX }}</a></li>
                    <li><a href="{{ route('app.client.view', $user) }}" wire:navigate>{{ $client->company_name }}</a></li>
                    <li>Edit</li>
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
                    <x-icon name="{{ $this->userRoleInfo['icon'] }}" class="h-5 text-warning" />
                </x-slot:menu>

                <div class="space-y-6">
                    {{-- Avatar & Basic Info --}}
                    <div class="text-center space-y-4">
                        {{-- Avatar Display --}}
                        <div class="flex justify-center">
                            <div class="avatar">
                                <div
                                    class="w-32 h-32 rounded-full ring ring-{{ $this->userStatusInfo['color'] }} ring-offset-base-100 ring-offset-4 hover:shadow-xl hover:shadow-primary transition-all duration-300">
                                    @if ($avatar)
                                        <img src="{{ $avatar->temporaryUrl() }}" alt="New Avatar"
                                            class="w-full h-full object-cover" />
                                    @elseif ($user->avatar_url)
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
                            <h3 class="text-xl font-bold text-base-content">{{ $name }}</h3>
                            <p class="text-base-content/70 break-all">{{ $email }}</p>

                            {{-- Status & Role Badges --}}
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
                                @if ($company_code)
                                    <div class="badge badge-{{ $this->clientUIConfig['colors']['company_code'] }} badge-lg">
                                        <x-icon name="{{ $this->clientUIConfig['icons']['company_code'] }}" class="h-4" />
                                        {{ $company_code }}
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- Company Display Name --}}
                    @if ($this->companyDisplayName)
                        <div class="bg-base-200 rounded-lg p-3">
                            <p class="text-sm font-medium text-base-content/70 mb-1">Nama Display Perusahaan</p>
                            <p class="font-semibold text-base-content">{{ $this->companyDisplayName }}</p>
                        </div>
                    @endif

                    {{-- Form Status Alert --}}
                    <div class="bg-{{ $this->formStatus['color'] }}/10 border border-{{ $this->formStatus['color'] }}/30 rounded-lg p-3">
                        <div class="flex items-center gap-2">
                            <x-icon name="{{ $this->formStatus['icon'] }}" class="w-4 h-4 text-{{ $this->formStatus['color'] }}" />
                            <span class="text-sm text-{{ $this->formStatus['color'] }} font-medium">{{ $this->formStatus['message'] }}</span>
                        </div>
                    </div>

                    {{-- Quick Actions --}}
                    <div class="space-y-2 pt-4 border-t border-base-300">
                        <x-button label="Data Client" wire:click="cancel" class="btn-primary btn-outline btn-block"
                            icon="{{ $this->clientUIConfig['icons']['back'] }}" />

                        <x-button label="Detail {{ $client->company_name }}"
                            link="{{ route('app.client.view', $user) }}" wire:navigate
                            class="btn-info btn-outline btn-block" icon="{{ $this->clientUIConfig['icons']['view'] }}" />

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
        <div class="lg:col-span-2">
            <x-card title="Edit Informasi Client {{ $user->name }}" separator class="shadow-md">
                <x-slot:menu>
                    <x-icon name="{{ $this->userRoleInfo['icon'] }}" class="h-5 text-warning" />
                </x-slot:menu>

                <x-form wire:submit="save">
                    <div class="space-y-6">
                        {{-- User Information --}}
                        <div class="space-y-4">
                            <h3 class="text-lg font-semibold text-base-content border-b border-warning pb-2">
                                Informasi Pengguna
                            </h3>

                            {{-- Avatar Upload --}}
                            <div class="space-y-4">
                                <label class="text-sm font-medium text-base-content">Foto Profil</label>

                                {{-- Avatar Display --}}
                                <div class="flex justify-center items-center gap-6">
                                    {{-- Current Avatar --}}
                                    <div class="text-center">
                                        <div class="avatar">
                                            <div
                                                class="w-20 h-20 rounded-full ring ring-base-300 ring-offset-base-100 ring-offset-2">
                                                @if ($user->avatar_url)
                                                    <img src="{{ Storage::url($user->avatar_url) }}" alt="Current" />
                                                @else
                                                    <div
                                                        class="w-full h-full bg-{{ $this->clientUIConfig['colors']['client_role'] }} text-{{ $this->clientUIConfig['colors']['client_role'] }}-content rounded-full flex items-center justify-center text-xl font-bold">
                                                        {{ $this->avatarPlaceholder }}
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                        <p class="text-xs text-base-content/50 mt-1">Current</p>
                                    </div>

                                    @if ($avatar)
                                        {{-- New Avatar Preview --}}
                                        <div class="text-center">
                                            <div class="avatar">
                                                <div
                                                    class="w-20 h-20 rounded-full ring ring-warning ring-offset-base-100 ring-offset-2">
                                                    <img src="{{ $avatar->temporaryUrl() }}" alt="New" />
                                                </div>
                                            </div>
                                            <p class="text-xs text-warning mt-1">New</p>
                                        </div>
                                    @endif
                                </div>

                                {{-- File Upload --}}
                                <x-file label="Upload Foto Profil Baru" wire:model="avatar" accept="image/*"
                                    hint="Opsional. Maksimal 2MB. Kosongkan jika tidak ingin mengubah." />
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                {{-- Name --}}
                                <x-input label="Nama Lengkap" wire:model.live.debounce.500ms="name" placeholder="{{ \App\Class\Helper\FormatHelper::PLACEHOLDER_NAME_USER }}"
                                    icon="{{ $this->clientUIConfig['icons']['name'] }}" clearable required />

                                {{-- Email --}}
                                <x-input label="Alamat Email" wire:model.live.debounce.500ms="email" placeholder="{{ \App\Class\Helper\FormatHelper::PLACEHOLDER_EMAIL }}"
                                    type="email" icon="{{ $this->clientUIConfig['icons']['email'] }}" clearable required />
                            </div>

                            {{-- Security --}}
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                {{-- Password --}}
                                <x-password label="Kata Sandi Baru" wire:model.live.debounce.500ms="password"
                                    placeholder="Minimal 8 karakter"
                                    hint="Kosongkan jika tidak ingin mengubah kata sandi" />

                                {{-- Password Confirmation --}}
                                <x-password label="Konfirmasi Kata Sandi" wire:model.live.debounce.500ms="password_confirmation"
                                    placeholder="Ulangi kata sandi baru" />
                            </div>
                        </div>

                        {{-- Company Information --}}
                        <div class="space-y-4">
                            <h3 class="text-lg font-semibold text-base-content border-b border-warning pb-2">
                                Informasi Perusahaan
                            </h3>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                {{-- Company Name --}}
                                <x-input label="Nama Perusahaan" wire:model.live.debounce.500ms="company_name"
                                    placeholder="{{ \App\Class\Helper\FormatHelper::PLACEHOLDER_COMPANY_NAME }}"
                                    icon="{{ $this->clientUIConfig['icons']['company_name'] }}" clearable required />

                                {{-- Company Code with Generate Button --}}
                                <div class="space-y-2">
                                    <x-input label="Kode Perusahaan" wire:model.live.debounce.300ms="company_code" placeholder="Kode perusahaan unik"
                                        icon="{{ $this->clientUIConfig['icons']['company_code'] }}" clearable>
                                        <x-slot:append>
                                            <x-button icon="phosphor.magic-wand" wire:click="generateCompanyCode"
                                                class="join-item btn-primary" tooltip="Generate Kode" />
                                        </x-slot:append>
                                    </x-input>
                                    <div class="text-xs text-base-content/50">
                                        Kode akan digenerate otomatis jika dikosongkan
                                    </div>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                {{-- Phone --}}
                                <x-input label="Telepon Perusahaan" wire:model.live.debounce.500ms="phone"
                                    placeholder="{{ \App\Class\Helper\FormatHelper::PLACEHOLDER_PHONE }}"
                                    icon="{{ $this->clientUIConfig['icons']['phone'] }}" clearable required />

                                {{-- Fax --}}
                                <x-input label="Fax Perusahaan" wire:model.live.debounce.500ms="fax"
                                    placeholder="{{ \App\Class\Helper\FormatHelper::PLACEHOLDER_PHONE }}"
                                    icon="{{ $this->clientUIConfig['icons']['fax'] }}" clearable />
                            </div>

                            {{-- Company Address --}}
                            <x-textarea label="Alamat Perusahaan" wire:model.live.debounce.500ms="company_address"
                                placeholder="{{ \App\Class\Helper\FormatHelper::PLACEHOLDER_COMPANY_ADDRESS }}"
                                icon="{{ $this->clientUIConfig['icons']['company_address'] }}" rows="3" required />

                            {{-- Tax ID --}}
                            <div>
                                <x-input label="NPWP" wire:model.live.debounce.500ms="tax_id"
                                    placeholder="{{ \App\Class\Helper\FormatHelper::PLACEHOLDER_TAX_ID }}"
                                    icon="{{ $this->clientUIConfig['icons']['tax_id'] }}" clearable />
                                <div class="text-xs text-base-content/50 mt-1">
                                    Format: XX.XXX.XXX.X-XXX.XXX (opsional)
                                </div>
                            </div>
                        </div>

                        {{-- Contact Person Information --}}
                        <div class="space-y-4">
                            <h3 class="text-lg font-semibold text-base-content border-b border-warning pb-2">
                                Informasi Contact Person
                            </h3>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                {{-- Contact Person --}}
                                <x-input label="Nama Contact Person" wire:model.live.debounce.500ms="contact_person"
                                    placeholder="{{ \App\Class\Helper\FormatHelper::PLACEHOLDER_CONTACT_PERSON }}"
                                    icon="{{ $this->clientUIConfig['icons']['contact_person'] }}" clearable required />

                                {{-- Contact Position --}}
                                <x-input label="Jabatan" wire:model.live.debounce.500ms="contact_position"
                                    placeholder="Jabatan di perusahaan"
                                    icon="{{ $this->clientUIConfig['icons']['contact_position'] }}" clearable />
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                {{-- Contact Phone --}}
                                <x-input label="Telepon Contact Person" wire:model.live.debounce.500ms="contact_phone"
                                    placeholder="{{ \App\Class\Helper\FormatHelper::PLACEHOLDER_PHONE }}"
                                    icon="{{ $this->clientUIConfig['icons']['contact_phone'] }}" clearable required />

                                {{-- Contact Email --}}
                                <x-input label="Email Contact Person" wire:model.live.debounce.500ms="contact_email"
                                    placeholder="{{ \App\Class\Helper\FormatHelper::PLACEHOLDER_EMAIL }}"
                                    icon="{{ $this->clientUIConfig['icons']['contact_email'] }}" clearable required />
                            </div>
                        </div>

                        {{-- Location Information --}}
                        <div class="space-y-4">
                            <h3 class="text-lg font-semibold text-base-content border-b border-warning pb-2">
                                Lokasi Perusahaan (Opsional)
                            </h3>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                {{-- Latitude --}}
                                <x-input label="Latitude" wire:model.live="company_latitude" placeholder="-6.200000" type="number" step="any"
                                    icon="{{ $this->clientUIConfig['icons']['coordinates'] }}" clearable />

                                {{-- Longitude --}}
                                <x-input label="Longitude" wire:model.live="company_longitude" placeholder="106.816666" type="number" step="any"
                                    icon="{{ $this->clientUIConfig['icons']['coordinates'] }}" clearable />
                            </div>

                            {{-- Coordinates Status --}}
                            @if ($company_latitude || $company_longitude)
                                <div class="bg-{{ $this->coordinatesStatus['color'] }}/20 rounded-lg p-3">
                                    <div class="flex items-center gap-2">
                                        <x-icon name="phosphor.map-pin" class="w-4 h-4 text-{{ $this->coordinatesStatus['color'] }}" />
                                        <span class="text-sm font-medium text-{{ $this->coordinatesStatus['color'] }}">{{ $this->coordinatesStatus['message'] }}</span>
                                    </div>
                                </div>
                            @endif

                            <div class="text-xs text-base-content/50">
                                Koordinat GPS untuk lokasi perusahaan (opsional). Pastikan dalam wilayah Indonesia.
                            </div>
                        </div>
                    </div>

                    {{-- Form Actions --}}
                    <x-slot:actions separator>
                        <x-button label="{{ \App\Class\Helper\FormatHelper::LABEL_CANCEL }}" wire:click="cancel" class="btn-ghost"
                            icon="{{ $this->clientUIConfig['icons']['close'] }}" />

                        @if ($this->hasChanges)
                            <x-button label="{{ \App\Class\Helper\FormatHelper::LABEL_RESET }}" wire:click="mount({{ $user->id }})" class="btn-warning"
                                icon="{{ $this->clientUIConfig['icons']['reset'] }}"
                                confirm="Yakin ingin reset? Semua perubahan akan hilang!" />
                        @endif

                        <x-button label="{{ \App\Class\Helper\FormatHelper::LABEL_UPDATE }} Client" type="submit" class="btn-success"
                            icon="{{ $this->clientUIConfig['icons']['edit'] }}" :disabled="!$this->isFormValid || !$this->hasChanges" spinner="save" />
                    </x-slot:actions>
                </x-form>
            </x-card>
        </div>
    </div>

    {{-- MODAL COMPONENTS --}}
    {{-- Change Status Modal --}}
    <livewire:app.component.user.change-status-modal />

    {{-- Delete User Modal --}}
    <livewire:app.component.user.delete-user-modal />
</div>
