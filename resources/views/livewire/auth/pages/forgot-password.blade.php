{{-- Forgot Password Page --}}
<div class="space-y-6">
    @if(!$linkSent)
        {{-- Header --}}
        <div class="text-center">
            <h1 class="text-2xl font-bold text-base-content mb-2">Lupa Password?</h1>
            <p class="text-base-content/70">Masukkan email Anda untuk mendapatkan link reset password</p>
        </div>

        {{-- Forgot Password Form Card --}}
        <x-card class="bg-base-200">
            <x-form wire:submit="sendResetLink" no-separator>
                {{-- Email Input --}}
                <x-input
                    label="Email"
                    wire:model.live.debounce.500ms="email"
                    type="email"
                    placeholder="Masukkan email Anda"
                    icon="phosphor.envelope"
                    autofocus
                    required
                    :disabled="$this->rateLimitInfo['is_limited']"
                />

                {{-- Submit Button --}}
                <x-button
                    type="submit"
                    class="btn-primary w-full"
                    size="lg"
                    wire:loading.attr="disabled"
                    wire:target="sendResetLink"
                    :disabled="$this->rateLimitInfo['is_limited']"
                >
                    <span wire:loading.remove wire:target="sendResetLink">
                        <x-icon name="phosphor.paper-plane-tilt" class="w-5 h-5 mr-2" />
                        @if($this->rateLimitInfo['is_limited'])
                            Coba Lagi dalam {{ $this->rateLimitInfo['minutes_until_reset'] }} Menit
                        @else
                            Kirim Link Reset Password
                            @if ($this->rateLimitInfo['remaining_attempts'] > 0 && $this->rateLimitInfo['remaining_attempts'] <= 2)
                                <span class="text-xs opacity-75">({{ $this->rateLimitInfo['remaining_attempts'] }} tersisa)</span>
                            @endif
                        @endif
                    </span>
                    <span wire:loading wire:target="sendResetLink">
                        <x-icon name="phosphor.spinner" class="w-5 h-5 mr-2 animate-spin" />
                        Mengirim...
                    </span>
                </x-button>

                {{-- Back to Login --}}
                <div class="text-center">
                    <x-button
                        wire:click="backToLogin"
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
                        <p class="mt-1">Setelah mencapai batas maksimal, Anda harus menunggu {{ $this->rateLimitInfo['limits']['window_minutes'] }} menit sebelum dapat mencoba lagi.</p>
                    </div>
                </div>
            </x-card>
        @endif
    @else
        {{-- Success State --}}
        <div class="text-center space-y-4">
            {{-- Success Icon --}}
            <div class="w-20 h-20 mx-auto mb-4 bg-success/20 rounded-full flex items-center justify-center">
                <x-icon name="phosphor.check-circle" class="w-10 h-10 text-success" />
            </div>

            <h1 class="text-2xl font-bold text-base-content mb-2">Link Telah Dikirim!</h1>
            <p class="text-base-content/70 mb-4">
                Kami telah mengirimkan link reset password ke email <strong>{{ $email }}</strong>
            </p>

            {{-- Instructions Card --}}
            <x-card title="Langkah Selanjutnya" subtitle="Ikuti petunjuk berikut">
                <div class="space-y-3 text-sm text-base-content/80">
                    <div class="flex items-center gap-3">
                        <span class="w-6 h-6 bg-primary text-primary-content rounded-full flex items-center justify-center text-xs font-bold">1</span>
                        <span>Periksa kotak masuk email Anda</span>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="w-6 h-6 bg-primary text-primary-content rounded-full flex items-center justify-center text-xs font-bold">2</span>
                        <span>Klik link yang ada di email</span>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="w-6 h-6 bg-primary text-primary-content rounded-full flex items-center justify-center text-xs font-bold">3</span>
                        <span>Masukkan password baru Anda</span>
                    </div>
                    <div class="flex items-start gap-3 mt-4 p-3 bg-warning/10 rounded-lg">
                        <x-icon name="phosphor.warning" class="w-5 h-5 text-warning mt-0.5 flex-shrink-0" />
                        <span class="text-warning font-medium">Link akan kadaluarsa dalam 60 menit</span>
                    </div>
                </div>
            </x-card>

            {{-- Action Buttons --}}
            <div class="flex flex-col sm:flex-row gap-3 justify-center">
                @if($this->rateLimitInfo['is_limited'])
                    <x-button
                        class="btn-outline"
                        type="button"
                        disabled
                    >
                        <x-icon name="phosphor.clock" class="w-4 h-4 mr-2" />
                        Coba Lagi dalam {{ $this->rateLimitInfo['minutes_until_reset'] }} Menit
                    </x-button>
                @else
                    <x-button
                        wire:click="resendLink"
                        class="btn-outline"
                        type="button"
                        wire:loading.attr="disabled"
                        wire:target="resendLink"
                    >
                        <span wire:loading.remove wire:target="resendLink">
                            <x-icon name="phosphor.paper-plane-tilt" class="w-4 h-4 mr-2" />
                            Kirim Ulang Link
                            @if ($this->rateLimitInfo['remaining_attempts'] > 0 && $this->rateLimitInfo['remaining_attempts'] <= 2)
                                <span class="text-xs opacity-75">({{ $this->rateLimitInfo['remaining_attempts'] }} tersisa)</span>
                            @endif
                        </span>
                        <span wire:loading wire:target="resendLink">
                            <x-icon name="phosphor.spinner" class="w-4 h-4 mr-2 animate-spin" />
                            Mengirim...
                        </span>
                    </x-button>
                @endif

                <x-button
                    wire:click="backToLogin"
                    class="btn-primary"
                    type="button"
                >
                    <x-icon name="phosphor.arrow-left" class="w-4 h-4 mr-2" />
                    Kembali ke Login
                </x-button>
            </div>
        </div>
    @endif

    {{-- Help/Tips Card --}}
    <x-card title="Tips & Bantuan" subtitle="Informasi berguna">
        <div class="space-y-3 text-sm text-base-content/70">
            <div class="flex items-start gap-3">
                <x-icon name="phosphor.lightbulb" class="w-4 h-4 text-warning mt-0.5 flex-shrink-0" />
                <span>Periksa folder spam/junk email jika tidak menerima email</span>
            </div>
            <div class="flex items-start gap-3">
                <x-icon name="phosphor.envelope" class="w-4 h-4 text-info mt-0.5 flex-shrink-0" />
                <span>Pastikan email yang dimasukkan sudah terdaftar</span>
            </div>
            <div class="flex items-start gap-3">
                <x-icon name="phosphor.clock" class="w-4 h-4 text-base-content/50 mt-0.5 flex-shrink-0" />
                <span>Tunggu beberapa menit, email mungkin tertunda</span>
            </div>
            @if($this->rateLimitInfo['is_limited'])
                <div class="flex items-start gap-3">
                    <x-icon name="phosphor.warning" class="w-4 h-4 text-error mt-0.5 flex-shrink-0" />
                    <span class="text-error font-medium">Anda sudah mencapai batas maksimal percobaan</span>
                </div>
            @endif
            <div class="flex items-start gap-3">
                <x-icon name="phosphor.shield-check" class="w-4 h-4 text-success mt-0.5 flex-shrink-0" />
                <span>Batas percobaan: Per email ({{ $this->rateLimitInfo['limits']['per_email'] }}x), Per IP ({{ $this->rateLimitInfo['limits']['per_ip'] }}x) dalam {{ $this->rateLimitInfo['limits']['window_minutes'] }} menit</span>
            </div>
        </div>

        <x-slot:actions separator>
            <div class="text-xs text-base-content/60">
                Masih bermasalah? Hubungi administrator di
                <a href="mailto:admin@delivtrack.test" class="link font-medium text-primary">admin@delivtrack.test</a>
            </div>
        </x-slot:actions>
    </x-card>
</div>
