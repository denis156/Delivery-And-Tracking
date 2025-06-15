{{-- Universal Login Page - Clean & Simple --}}
<div class="space-y-6">
    {{-- Header --}}
    <div class="text-center">
        <h1 class="text-2xl font-bold text-base-content mb-2">Masuk ke {{ config('app.name') }}</h1>
        <p class="text-base-content/70">Gunakan akun Anda untuk mengakses sistem</p>
    </div>

    {{-- Login Form Card --}}
    <x-card class="bg-base-200">
        <x-form wire:submit="login" no-separator>
            {{-- Email Input --}}
            <x-input label="Email" wire:model.live.debounce.500ms="email" type="email" placeholder="Masukan email" icon="phosphor.envelope"
                autofocus required :disabled="$this->rateLimitInfo['is_limited']" />

            {{-- Password Input --}}
            <x-password label="Password" wire:model="password" placeholder="Masukkan password" icon="phosphor.lock"
                right required :disabled="$this->rateLimitInfo['is_limited']" />

            {{-- Remember Me & Forgot Password --}}
            <div class="flex items-center justify-between">
                <x-checkbox wire:model="remember" label="Ingat saya" class="checkbox-primary" />

                <a href="{{ route('password.request') }}" class="text-sm text-primary hover:underline" wire:navigate>
                    Lupa password?
                </a>
            </div>

            {{-- Login Button --}}
            <x-button type="submit" class="btn-primary w-full" size="lg" wire:loading.attr="disabled"
                wire:target="login" :disabled="$this->rateLimitInfo['is_limited']">
                <span wire:loading.remove wire:target="login">
                    <x-icon name="phosphor.sign-in" class="w-5 h-5 mr-2" />
                    @if ($this->rateLimitInfo['is_limited'])
                        Coba Lagi dalam {{ $this->rateLimitInfo['minutes_until_reset'] }} Menit
                    @else
                        Masuk ke Sistem
                        @if ($this->rateLimitInfo['remaining_attempts'] > 0 && $this->rateLimitInfo['remaining_attempts'] <= 2)
                            <span class="text-xs opacity-75">({{ $this->rateLimitInfo['remaining_attempts'] }} tersisa)</span>
                        @endif
                    @endif
                </span>
                <span wire:loading wire:target="login">
                    <x-icon name="phosphor.spinner" class="w-5 h-5 mr-2 animate-spin" />
                    Memproses...
                </span>
            </x-button>

            {{-- Demo Buttons (Development Only) --}}
            @if (app()->environment('local'))
                <div class="divider text-xs">Demo Accounts</div>

                <div class="grid grid-cols-2 gap-2">
                    <x-button wire:click="fillDemoAdmin" class="btn-outline btn-sm" type="button"
                        wire:loading.attr="disabled" wire:target="fillDemoAdmin">
                        <x-icon name="phosphor.shield-check" class="w-4 h-4 mr-1" />
                        Admin
                    </x-button>

                    <x-button wire:click="fillDemoManager" class="btn-outline btn-sm" type="button"
                        wire:loading.attr="disabled" wire:target="fillDemoManager">
                        <x-icon name="phosphor.briefcase" class="w-4 h-4 mr-1" />
                        Manager
                    </x-button>

                    <x-button wire:click="fillDemoDriver" class="btn-outline btn-sm" type="button"
                        wire:loading.attr="disabled" wire:target="fillDemoDriver">
                        <x-icon name="phosphor.truck" class="w-4 h-4 mr-1" />
                        Driver
                    </x-button>

                    <x-button wire:click="fillDemoClient" class="btn-outline btn-sm" type="button"
                        wire:loading.attr="disabled" wire:target="fillDemoClient">
                        <x-icon name="phosphor.user" class="w-4 h-4 mr-1" />
                        Client
                    </x-button>
                </div>
            @endif
        </x-form>
    </x-card>

    {{-- Rate Limit Warning Card --}}
    @if ($this->rateLimitInfo['should_show_warning'])
        <x-card title="Informasi Batas Percobaan" subtitle="Perhatian" class="border-warning/30">
            <div class="flex items-start gap-3 text-sm">
                <x-icon name="phosphor.info" class="w-5 h-5 text-warning mt-0.5 flex-shrink-0" />
                <div class="text-base-content/70">
                    <p class="font-medium text-warning">Anda memiliki {{ $this->rateLimitInfo['remaining_attempts'] }} percobaan tersisa.</p>
                    <p class="mt-1">Setelah {{ $this->rateLimitInfo['limits']['per_email'] }} percobaan gagal, akun akan dibatasi selama {{ $this->rateLimitInfo['limits']['window_minutes'] }} menit.</p>
                </div>
            </div>
        </x-card>
    @endif

    {{-- Security Info Card --}}
    @if ($this->rateLimitInfo['is_limited'])
        <x-card title="Keamanan Akun" subtitle="Akun Anda dilindungi" class="border-error/30">
            <div class="space-y-2 text-sm text-base-content/70">
                <div class="flex items-start gap-3">
                    <x-icon name="phosphor.shield-warning" class="w-4 h-4 text-error mt-0.5 flex-shrink-0" />
                    <span class="text-error font-medium">Terlalu banyak percobaan login yang gagal</span>
                </div>
                <div class="flex items-start gap-3">
                    <x-icon name="phosphor.clock" class="w-4 h-4 text-base-content/50 mt-0.5 flex-shrink-0" />
                    <span>Tunggu {{ $this->rateLimitInfo['minutes_until_reset'] }} menit sebelum mencoba lagi</span>
                </div>
                <div class="flex items-start gap-3">
                    <x-icon name="phosphor.shield-check" class="w-4 h-4 text-success mt-0.5 flex-shrink-0" />
                    <span>Batas login: {{ $this->rateLimitInfo['limits']['per_email'] }} percobaan per {{ $this->rateLimitInfo['limits']['window_minutes'] }} menit per email</span>
                </div>
            </div>
        </x-card>
    @endif

    {{-- Account Information Card --}}
    <x-card title="Butuh Akun?" subtitle="Untuk pengguna baru">
        <div class="flex items-center gap-2 text-sm text-base-content/80">
            <x-icon name="phosphor.user-plus" class="w-4 h-4 text-info" />
            <span>Untuk membuat akun baru, silakan hubungi administrator sistem.</span>
        </div>
    </x-card>

    {{-- Support --}}
    <div class="text-center text-xs text-base-content/60">
        Butuh bantuan? Hubungi administrator di
        <a href="mailto:admin@delivtrack.test" class="link font-medium">admin@delivtrack.test</a>
    </div>
</div>
