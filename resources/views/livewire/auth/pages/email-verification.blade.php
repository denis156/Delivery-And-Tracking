{{-- Email Verification Page --}}
<div class="space-y-6" >
    @if (!$emailVerified)
        {{-- Header --}}
        <div class="text-center">
            {{-- Email Icon --}}
            <div class="w-20 h-20 mx-auto mb-4 bg-warning/20 rounded-full flex items-center justify-center">
                <x-icon name="phosphor.envelope-simple" class="w-10 h-10 text-warning" />
            </div>

            <h1 class="text-2xl font-bold text-base-content mb-2">Verifikasi Email Anda</h1>
            <p class="text-base-content/70">
                Kami telah mengirimkan email verifikasi ke <strong>{{ auth()->user()->email }}</strong>
            </p>
        </div>

        {{-- Instructions Card --}}
        <x-card title="Langkah Verifikasi" subtitle="Ikuti petunjuk berikut" class="bg-base-200" >
            <div class="space-y-3 text-sm text-base-content/80">
                <div class="flex items-center gap-3">
                    <span
                        class="w-6 h-6 bg-primary text-primary-content rounded-full flex items-center justify-center text-xs font-bold">1</span>
                    <span>Periksa kotak masuk email Anda</span>
                </div>
                <div class="flex items-center gap-3">
                    <span
                        class="w-6 h-6 bg-primary text-primary-content rounded-full flex items-center justify-center text-xs font-bold">2</span>
                    <span>Klik link verifikasi yang ada di email</span>
                </div>
                <div class="flex items-center gap-3">
                    <span
                        class="w-6 h-6 bg-primary text-primary-content rounded-full flex items-center justify-center text-xs font-bold">3</span>
                    <span>Anda akan otomatis diarahkan ke dashboard</span>
                </div>
                <div class="flex items-start gap-3 mt-4 p-3 bg-warning/10 rounded-lg">
                    <x-icon name="phosphor.warning" class="w-5 h-5 text-warning mt-0.5 flex-shrink-0" />
                    <span class="text-warning font-medium">Pastikan cek folder spam jika tidak menemukan email</span>
                </div>
            </div>
        </x-card>

        {{-- Action Buttons Card --}}
        <x-card class="bg-base-200" >
            <div class="flex flex-col sm:flex-row gap-3 justify-center">
                @if ($this->rateLimitInfo['is_limited'])
                    <x-button class="btn-primary" type="button" disabled>
                        <x-icon name="phosphor.clock" class="w-5 h-5 mr-2" />
                        Coba Lagi dalam {{ $this->rateLimitInfo['minutes_until_reset'] }} Menit
                    </x-button>
                @else
                    <x-button wire:click="resendVerification" class="btn-primary" type="button"
                        wire:loading.attr="disabled" wire:target="resendVerification">
                        <span wire:loading.remove wire:target="resendVerification">
                            <x-icon name="phosphor.paper-plane-tilt" class="w-5 h-5 mr-2" />
                            Kirim Ulang Email
                            @if ($this->rateLimitInfo['remaining_attempts'] > 0 && $this->rateLimitInfo['remaining_attempts'] <= 2)
                                <span class="text-xs opacity-75">({{ $this->rateLimitInfo['remaining_attempts'] }} tersisa)</span>
                            @endif
                        </span>
                        <span wire:loading wire:target="resendVerification">
                            <x-icon name="phosphor.spinner" class="w-5 h-5 mr-2 animate-spin" />
                            Mengirim...
                        </span>
                    </x-button>
                @endif

                <x-button wire:click="logout" class="btn-outline" type="button">
                    <x-icon name="phosphor.sign-out" class="w-5 h-5 mr-2" />
                    Logout
                </x-button>
            </div>
        </x-card>

        {{-- Rate Limit Warning Card --}}
        @if ($this->rateLimitInfo['should_show_warning'])
            <x-card title="Informasi Batas Percobaan" subtitle="Perhatian" class="border-warning/30">
                <div class="flex items-start gap-3 text-sm">
                    <x-icon name="phosphor.info" class="w-5 h-5 text-warning mt-0.5 flex-shrink-0" />
                    <div class="text-base-content/70">
                        <p class="font-medium text-warning">Anda memiliki {{ $this->rateLimitInfo['remaining_attempts'] }} percobaan tersisa.</p>
                        <p class="mt-1">Setelah {{ $this->rateLimitInfo['limits']['per_user'] }} percobaan, Anda harus menunggu {{ $this->rateLimitInfo['limits']['window_minutes'] }} menit sebelum dapat mencoba lagi.</p>
                    </div>
                </div>
            </x-card>
        @endif

        {{-- Help/Tips Card --}}
        <x-card title="Tips & Bantuan" subtitle="Informasi berguna">
            <div class="space-y-2 text-sm text-base-content/70">
                <div class="flex items-start gap-2">
                    <x-icon name="phosphor.envelope" class="w-4 h-4 text-info mt-0.5 flex-shrink-0" />
                    <span>Periksa folder spam/junk email Anda</span>
                </div>
                <div class="flex items-start gap-2">
                    <x-icon name="phosphor.check-circle" class="w-4 h-4 text-success mt-0.5 flex-shrink-0" />
                    <span>Pastikan email {{ auth()->user()->email }} dapat menerima email</span>
                </div>
                <div class="flex items-start gap-2">
                    <x-icon name="phosphor.clock" class="w-4 h-4 text-base-content/50 mt-0.5 flex-shrink-0" />
                    <span>Tunggu beberapa menit, email mungkin tertunda</span>
                </div>
                @if ($this->rateLimitInfo['is_limited'])
                    <div class="flex items-start gap-2">
                        <x-icon name="phosphor.warning" class="w-4 h-4 text-error mt-0.5 flex-shrink-0" />
                        <span class="text-error font-medium">Anda sudah mencapai batas maksimal percobaan ({{ $this->rateLimitInfo['limits']['per_user'] }}x dalam {{ $this->rateLimitInfo['limits']['window_minutes'] }} menit)</span>
                    </div>
                @endif
                <div class="flex items-start gap-2">
                    <x-icon name="phosphor.shield-check" class="w-4 h-4 text-success mt-0.5 flex-shrink-0" />
                    <span>Batas percobaan: {{ $this->rateLimitInfo['limits']['per_user'] }} kali per {{ $this->rateLimitInfo['limits']['window_minutes'] }} menit</span>
                </div>
            </div>

            <x-slot:actions separator>
                <div class="text-xs text-base-content/60">
                    Masih bermasalah? Hubungi administrator di
                    <a href="mailto:admin@delivtrack.test"
                        class="link font-medium text-primary">admin@delivtrack.test</a>
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

            <h1 class="text-2xl font-bold text-base-content mb-2">Email Berhasil Diverifikasi!</h1>
            <p class="text-base-content/70 mb-4">
                Selamat! Email Anda telah berhasil diverifikasi. Sekarang Anda dapat mengakses semua fitur aplikasi.
            </p>

            {{-- Welcome Card --}}
            <x-card title="Selamat Datang!" subtitle="Akun Anda siap digunakan">
                <div class="flex items-center gap-3 text-sm text-base-content/80">
                    <x-icon name="phosphor.confetti" class="w-6 h-6 text-success flex-shrink-0" />
                    <span>
                        Halo <strong>{{ auth()->user()->name }}</strong>!
                        Akun Anda sebagai <strong>{{ auth()->user()->role_label }}</strong> telah siap digunakan.
                    </span>
                </div>

                <x-slot:actions separator>
                    <x-button wire:click="continueToApp" class="btn-primary btn-wide" type="button">
                        <x-icon name="phosphor.house" class="w-5 h-5 mr-2" />
                        Masuk ke Dashboard
                    </x-button>
                </x-slot:actions>
            </x-card>
        </div>
    @endif
</div>
