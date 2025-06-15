{{-- Reset Password Page --}}
<div class="space-y-6">
    @if(!$passwordReset)
        {{-- Header --}}
        <div class="text-center">
            <h1 class="text-2xl font-bold text-base-content mb-2">Reset Password</h1>
            <p class="text-base-content/70">Masukkan password baru untuk akun Anda</p>
        </div>

        {{-- Reset Password Form Card --}}
        <x-card class="bg-base-200">
            <x-form wire:submit="resetPassword" no-separator>
                {{-- Email Input (readonly) --}}
                <x-input
                    label="Email"
                    wire:model="email"
                    type="email"
                    placeholder="Email Anda"
                    icon="phosphor.envelope"
                    readonly
                    required
                />

                {{-- Password Input --}}
                <x-password
                    label="Password Baru"
                    wire:model="password"
                    placeholder="Masukkan password baru"
                    icon="phosphor.lock"
                    right
                    required
                    hint="Minimal 8 karakter"
                    :disabled="$this->rateLimitInfo['is_limited']"
                />

                {{-- Password Confirmation Input --}}
                <x-password
                    label="Konfirmasi Password"
                    wire:model="password_confirmation"
                    placeholder="Konfirmasi password baru"
                    icon="phosphor.lock"
                    right
                    required
                    :disabled="$this->rateLimitInfo['is_limited']"
                />

                {{-- Submit Button --}}
                <x-button
                    type="submit"
                    class="btn-primary w-full"
                    size="lg"
                    wire:loading.attr="disabled"
                    wire:target="resetPassword"
                    :disabled="$this->rateLimitInfo['is_limited']"
                >
                    <span wire:loading.remove wire:target="resetPassword">
                        <x-icon name="phosphor.lock" class="w-5 h-5 mr-2" />
                        @if($this->rateLimitInfo['is_limited'])
                            Coba Lagi dalam {{ $this->rateLimitInfo['minutes_until_reset'] }} Menit
                        @else
                            Reset Password
                            @if ($this->rateLimitInfo['remaining_attempts'] > 0 && $this->rateLimitInfo['remaining_attempts'] <= 2)
                                <span class="text-xs opacity-75">({{ $this->rateLimitInfo['remaining_attempts'] }} tersisa)</span>
                            @endif
                        @endif
                    </span>
                    <span wire:loading wire:target="resetPassword">
                        <x-icon name="phosphor.spinner" class="w-5 h-5 mr-2 animate-spin" />
                        Mereset Password...
                    </span>
                </x-button>

                {{-- Actions --}}
                <div class="flex flex-col sm:flex-row gap-3 justify-center text-center">
                    <x-button
                        wire:click="requestNewLink"
                        class="btn-ghost btn-sm"
                        type="button"
                    >
                        <x-icon name="phosphor.paper-plane-tilt" class="w-4 h-4 mr-1" />
                        Minta Link Baru
                    </x-button>

                    <x-button
                        wire:click="goToLogin"
                        class="btn-ghost btn-sm"
                        type="button"
                    >
                        <x-icon name="phosphor.arrow-left" class="w-4 h-4 mr-1" />
                        Kembali ke Login
                    </x-button>
                </div>
            </x-form>
        </x-card>

        {{-- Rate Limit Warning Card --}}
        @if ($this->rateLimitInfo['should_show_warning'])
            <x-card title="Informasi Batas Percobaan" subtitle="Perhatian" class="border-warning/30">
                <div class="flex items-start gap-3 text-sm">
                    <x-icon name="phosphor.info" class="w-5 h-5 text-warning mt-0.5 flex-shrink-0" />
                    <div class="text-base-content/70">
                        <p class="font-medium text-warning">Anda memiliki {{ $this->rateLimitInfo['remaining_attempts'] }} percobaan tersisa.</p>
                        <p class="mt-1">Setelah {{ $this->rateLimitInfo['limits']['per_token'] }} percobaan, token akan dibatasi selama {{ $this->rateLimitInfo['limits']['window_minutes'] }} menit.</p>
                    </div>
                </div>
            </x-card>
        @endif

        {{-- Password Requirements Card --}}
        <x-card title="Persyaratan Password" subtitle="Pastikan password Anda aman">
            <div class="space-y-2 text-sm text-base-content/80">
                <div class="flex items-center gap-2">
                    <x-icon name="phosphor.check" class="w-4 h-4 text-success" />
                    <span>Minimal 8 karakter</span>
                </div>
                <div class="flex items-center gap-2">
                    <x-icon name="phosphor.check" class="w-4 h-4 text-success" />
                    <span>Kombinasi huruf dan angka disarankan</span>
                </div>
                <div class="flex items-center gap-2">
                    <x-icon name="phosphor.check" class="w-4 h-4 text-success" />
                    <span>Hindari password yang mudah ditebak</span>
                </div>
            </div>
        </x-card>

        {{-- Security Card --}}
        <x-card title="Keamanan" subtitle="Informasi keamanan penting">
            <div class="space-y-2 text-sm text-base-content/70">
                <div class="flex items-start gap-2">
                    <x-icon name="phosphor.clock" class="w-4 h-4 text-warning mt-0.5 flex-shrink-0" />
                    <span>Link reset password hanya berlaku 60 menit</span>
                </div>
                <div class="flex items-start gap-2">
                    <x-icon name="phosphor.shield-check" class="w-4 h-4 text-info mt-0.5 flex-shrink-0" />
                    <span>Jangan bagikan link ini ke orang lain</span>
                </div>
                <div class="flex items-start gap-2">
                    <x-icon name="phosphor.device-mobile" class="w-4 h-4 text-success mt-0.5 flex-shrink-0" />
                    <span>Pastikan Anda login dari perangkat yang aman</span>
                </div>
                @if($this->rateLimitInfo['is_limited'])
                    <div class="flex items-start gap-2">
                        <x-icon name="phosphor.warning" class="w-4 h-4 text-error mt-0.5 flex-shrink-0" />
                        <span class="text-error font-medium">Anda sudah mencapai batas maksimal percobaan</span>
                    </div>
                @endif
                <div class="flex items-start gap-2">
                    <x-icon name="phosphor.shield-check" class="w-4 h-4 text-success mt-0.5 flex-shrink-0" />
                    <span>Batas reset: {{ $this->rateLimitInfo['limits']['per_token'] }} percobaan per {{ $this->rateLimitInfo['limits']['window_minutes'] }} menit</span>
                </div>
            </div>

            <x-slot:actions separator>
                <div class="text-xs text-base-content/60">
                    Masih bermasalah? Hubungi administrator di
                    <a href="mailto:admin@delivtrack.test" class="link font-medium text-primary">admin@delivtrack.test</a>
                </div>
            </x-slot:actions>
        </x-card>
    @else
        {{-- Success State --}}
        <div class="text-center space-y-4">
            {{-- Success Icon --}}
            <div class="w-20 h-20 mx-auto mb-4 bg-success/20 rounded-full flex items-center justify-center">
                <x-icon name="phosphor.check-circle" class="w-10 h-10 text-success" />
            </div>

            <h1 class="text-2xl font-bold text-base-content mb-2">Password Berhasil Direset!</h1>
            <p class="text-base-content/70 mb-4">
                Password Anda telah berhasil diubah. Sekarang Anda dapat login dengan password baru.
            </p>

            {{-- Instructions Card --}}
            <x-card title="Langkah Selanjutnya" subtitle="Silakan login dengan password baru">
                <div class="space-y-2 text-sm text-base-content/80">
                    <div class="flex items-center gap-2">
                        <span class="w-6 h-6 bg-primary text-primary-content rounded-full flex items-center justify-center text-xs font-bold">1</span>
                        <span>Klik tombol "Login Sekarang" di bawah</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="w-6 h-6 bg-primary text-primary-content rounded-full flex items-center justify-center text-xs font-bold">2</span>
                        <span>Masukkan email dan password baru Anda</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="w-6 h-6 bg-primary text-primary-content rounded-full flex items-center justify-center text-xs font-bold">3</span>
                        <span>Anda akan diarahkan ke dashboard</span>
                    </div>
                </div>

                <x-slot:actions separator>
                    <x-button
                        wire:click="goToLogin"
                        class="btn-primary btn-wide"
                        type="button"
                    >
                        <x-icon name="phosphor.sign-in" class="w-5 h-5 mr-2" />
                        Login Sekarang
                    </x-button>
                </x-slot:actions>
            </x-card>
        </div>
    @endif
</div>
