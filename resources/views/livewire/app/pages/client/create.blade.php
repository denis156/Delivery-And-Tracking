{{-- Create Client Page - Mary UI + DaisyUI Standards --}}
<div>
    {{-- HEADER --}}
    <x-header title="{{ \App\Class\Helper\ClientHelper::PAGE_TITLE_CREATE }}" subtitle="{{ \App\Class\Helper\ClientHelper::PAGE_SUBTITLE_CREATE }}"
        icon="{{ $this->clientUIConfig['icons']['add'] }}" icon-classes="text-success h-10" separator progress-indicator>
        <x-slot:middle class="!justify-end">
            <div class="breadcrumbs text-sm hidden lg:block">
                <ul>
                    <li><a href="{{ route('app.client.index') }}" wire:navigate>{{ \App\Class\Helper\ClientHelper::PAGE_TITLE_INDEX }}</a></li>
                    <li>{{ \App\Class\Helper\ClientHelper::PAGE_TITLE_CREATE }}</li>
                </ul>
            </div>
        </x-slot:middle>
    </x-header>

    {{-- MAIN CONTENT --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- CLIENT PROFILE CARD --}}
        <div class="lg:col-span-1">
            <x-card title="Preview Client" separator sticky class="shadow-md">
                <x-slot:menu>
                    <x-icon name="{{ $this->clientUIConfig['icons']['user'] }}" class="h-5 text-success" />
                </x-slot:menu>

                <div class="space-y-6">
                    {{-- Avatar & Basic Info --}}
                    <div class="text-center space-y-4">
                        {{-- Avatar Display --}}
                        <div class="flex justify-center">
                            <div class="avatar">
                                <div
                                    class="w-32 h-32 rounded-full ring ring-{{ $is_active ? 'success' : 'warning' }} ring-offset-base-100 ring-offset-4 hover:shadow-xl hover:shadow-primary transition-all duration-300">
                                    @if ($avatar)
                                        <img src="{{ $avatar->temporaryUrl() }}" alt="Avatar"
                                            class="w-full h-full object-cover" />
                                    @else
                                        <div
                                            class="w-full h-full bg-{{ $this->clientUIConfig['colors']['client_role'] }} text-{{ $this->clientUIConfig['colors']['client_role'] }}-content rounded-full flex items-center justify-center text-3xl font-bold">
                                            {{ $name ? strtoupper(substr($name, 0, 2)) : 'C' }}
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- Name & Email Display --}}
                        <div>
                            <h3 class="text-xl font-bold text-base-content">
                                {{ $name ?: 'Nama Client' }}
                            </h3>
                            <p class="text-base-content/70 break-all">
                                {{ $email ?: 'email@example.com' }}
                            </p>

                            {{-- Status & Role Badges --}}
                            <div class="flex flex-wrap justify-center gap-2 mt-3">
                                <div class="badge badge-{{ $this->clientUIConfig['colors']['client_role'] }} badge-lg">
                                    <x-icon name="{{ $this->clientUIConfig['icons']['back'] }}" class="h-4" />
                                    Client
                                </div>
                                <div class="badge badge-{{ $is_active ? 'success' : 'warning' }} badge-lg">
                                    <x-icon name="{{ $is_active ? \App\Class\Helper\UserHelper::getStatusIcon('active') : \App\Class\Helper\UserHelper::getStatusIcon('inactive') }}"
                                        class="h-4" />
                                    {{ $is_active ? 'Aktif' : 'Nonaktif' }}
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Company Info Preview --}}
                    <div class="space-y-3 pt-4 border-t border-base-300">
                        <h4 class="font-semibold text-base-content flex items-center gap-2">
                            <x-icon name="{{ $this->clientUIConfig['icons']['company_name'] }}" class="w-4 h-4 text-{{ $this->clientUIConfig['colors']['company_name'] }}" />
                            Informasi Perusahaan
                        </h4>

                        {{-- Company Name --}}
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-base-content/60">Nama:</span>
                            <span class="text-sm font-medium text-base-content truncate ml-2">
                                {{ $company_name ?: 'Nama Perusahaan' }}
                            </span>
                        </div>

                        {{-- Company Code --}}
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-base-content/60">Kode:</span>
                            <x-badge :value="$company_code ?: 'AUTO-GENERATE'"
                                class="badge-{{ $this->clientUIConfig['colors']['company_code'] }} badge-outline badge-sm"
                                wire:key="company-code-{{ $company_code ?: 'auto' }}" />
                        </div>

                        {{-- Contact Person --}}
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-base-content/60">Contact:</span>
                            <span class="text-sm font-medium text-base-content truncate ml-2">
                                {{ $contact_person ?: 'Contact Person' }}
                            </span>
                        </div>

                        {{-- Company Display Name --}}
                        @if ($company_name || $company_code)
                            <div class="pt-2 border-t border-base-300">
                                <span class="text-xs text-base-content/50">Display Name:</span>
                                <p class="text-sm font-medium text-primary">{{ $this->companyDisplayName }}</p>
                            </div>
                        @endif
                    </div>

                    {{-- Form Status Alert --}}
                    @if ($this->isFormValid)
                        <div class="bg-success/10 border border-success/30 rounded-lg p-3">
                            <div class="flex items-center gap-2">
                                <x-icon name="phosphor.check-circle" class="w-4 h-4 text-success" />
                                <span class="text-sm text-success font-medium">Siap untuk disimpan!</span>
                            </div>
                        </div>
                    @elseif($this->hasData)
                        <div class="bg-warning/10 border border-warning/30 rounded-lg p-3">
                            <div class="flex items-center gap-2">
                                <x-icon name="phosphor.warning" class="w-4 h-4 text-warning" />
                                <span class="text-sm text-warning font-medium">Lengkapi semua field wajib</span>
                            </div>
                        </div>
                    @else
                        <div class="bg-info/10 border border-info/30 rounded-lg p-3">
                            <div class="flex items-center gap-2">
                                <x-icon name="phosphor.info" class="w-4 h-4 text-info" />
                                <span class="text-sm text-info font-medium">Mulai mengisi form untuk preview</span>
                            </div>
                        </div>
                    @endif

                    {{-- Quick Actions --}}
                    <div class="space-y-2 pt-4 border-t border-base-300">
                        <x-button label="Data Client" wire:click="cancel" class="btn-primary btn-block"
                            icon="{{ $this->clientUIConfig['icons']['back'] }}" />

                        <x-button label="{{ \App\Class\Helper\FormatHelper::LABEL_RESET }} Form" wire:click="resetForm" class="btn-secondary btn-block"
                            icon="phosphor.arrow-counter-clockwise" :disabled="!$this->hasData" />

                        <x-button :label="$is_active ? 'Set: Nonaktifkan' : 'Set: Aktifkan'"
                            wire:click="toggleUserStatus"
                            class="btn-{{ $is_active ? 'warning' : 'success' }} btn-outline btn-block"
                            :icon="$is_active ? \App\Class\Helper\UserHelper::getStatusIcon('inactive') : \App\Class\Helper\UserHelper::getStatusIcon('active')" :disabled="empty($name) || empty($email)" />
                    </div>
                </div>
            </x-card>
        </div>

        {{-- FORM SECTION --}}
        <div class="lg:col-span-2">
            <x-card title="Informasi Client Baru" separator class="shadow-md">
                <x-slot:menu>
                    <x-icon name="phosphor.plus-circle" class="h-5 text-success" />
                </x-slot:menu>

                <x-form wire:submit="save">
                    <div class="space-y-6">
                        {{-- User Information --}}
                        <div class="space-y-4">
                            <h3 class="text-lg font-semibold text-base-content border-b border-success pb-2">
                                Informasi Akun Client
                            </h3>

                            {{-- Avatar Upload with Preview --}}
                            <div class="space-y-4">
                                <label class="text-sm font-medium text-base-content">Foto Profil</label>

                                {{-- Avatar Display --}}
                                <div class="flex justify-center items-center gap-6">
                                    {{-- Current Avatar --}}
                                    <div class="text-center">
                                        <div class="avatar">
                                            <div
                                                class="w-20 h-20 rounded-full ring ring-base-300 ring-offset-base-100 ring-offset-2">
                                                @if ($avatar)
                                                    <img src="{{ $avatar->temporaryUrl() }}" alt="Avatar" />
                                                @else
                                                    <div
                                                        class="w-full h-full bg-{{ $this->clientUIConfig['colors']['client_role'] }} text-{{ $this->clientUIConfig['colors']['client_role'] }}-content rounded-full flex items-center justify-center text-xl font-bold">
                                                        {{ $name ? strtoupper(substr($name, 0, 2)) : 'C' }}
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                        <p class="text-xs text-base-content/50 mt-1">Preview</p>
                                    </div>
                                </div>

                                {{-- File Upload --}}
                                <x-file label="Upload Foto Profil" wire:model="avatar" accept="image/*"
                                    hint="Opsional. Maksimal 2MB. Format: JPEG, JPG, PNG, WebP" />
                            </div>

                            {{-- Name --}}
                            <x-input label="Nama Lengkap" wire:model.live.debounce.500ms="name" placeholder="{{ \App\Class\Helper\FormatHelper::PLACEHOLDER_NAME_USER }}"
                                icon="{{ $this->clientUIConfig['icons']['name'] }}" clearable required />

                            {{-- Email --}}
                            <x-input label="Alamat Email" wire:model.live.debounce.500ms="email" placeholder="{{ \App\Class\Helper\FormatHelper::PLACEHOLDER_EMAIL }}"
                                type="email" icon="{{ $this->clientUIConfig['icons']['email'] }}" clearable required />

                            {{-- Security --}}
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                {{-- Password --}}
                                <x-password label="Kata Sandi" wire:model.live.debounce.500ms="password"
                                    placeholder="Minimal 8 karakter"
                                    hint="Kombinasi huruf besar, kecil, angka, dan simbol" required />

                                {{-- Password Confirmation --}}
                                <x-password label="Konfirmasi Kata Sandi" wire:model.live.debounce.500ms="password_confirmation"
                                    placeholder="Ulangi kata sandi" required />
                            </div>
                        </div>

                        {{-- Company Information --}}
                        <div class="space-y-4">
                            <h3 class="text-lg font-semibold text-base-content border-b border-success pb-2">
                                Informasi Perusahaan
                            </h3>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                {{-- Company Name --}}
                                <x-input label="Nama Perusahaan" wire:model.live.debounce.500ms="company_name"
                                    placeholder="{{ \App\Class\Helper\FormatHelper::PLACEHOLDER_COMPANY_NAME }}"
                                    icon="{{ $this->clientUIConfig['icons']['company_name'] }}" clearable required />

                                {{-- Company Code with Generate Button --}}
                                <div class="space-y-2">
                                    <x-input label="Kode Perusahaan" wire:model.live.debounce.300ms="company_code" placeholder="Kosongkan untuk generate otomatis"
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
                            <h3 class="text-lg font-semibold text-base-content border-b border-success pb-2">
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
                            <h3 class="text-lg font-semibold text-base-content border-b border-success pb-2">
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
                        <x-button label="{{ \App\Class\Helper\FormatHelper::LABEL_SAVE }} Client" type="submit" class="btn-primary" icon="{{ $this->clientUIConfig['icons']['add'] }}"
                            :disabled="!$this->isFormValid" spinner="save" />
                    </x-slot:actions>
                </x-form>
            </x-card>
        </div>
    </div>
</div>
