{{-- Email Verification Handler Page --}}
<div class="space-y-6">
    @if ($verificationProcessed)
        @if ($verificationSuccess)
            {{-- Success State --}}
            <div class="text-center space-y-4">
                {{-- Success Icon --}}
                <div class="w-20 h-20 mx-auto mb-4 bg-success/20 rounded-full flex items-center justify-center">
                    <x-icon name="phosphor.check-circle" class="w-10 h-10 text-success" />
                </div>

                <h1 class="text-2xl font-bold text-base-content mb-2">Email Berhasil Diverifikasi!</h1>
                <p class="text-base-content/70 mb-4">{{ $message }}</p>

                {{-- Welcome Card --}}
                <x-card title="Selamat Datang!" subtitle="Akun Anda telah aktif" class="bg-base-200">
                    <div class="flex items-center gap-3 text-sm text-base-content/80">
                        <x-icon name="phosphor.confetti" class="w-6 h-6 text-success flex-shrink-0" />
                        <span>
                            Halo <strong>{{ auth()->user()->name }}</strong>!
                            Akun Anda sebagai <strong>{{ auth()->user()->role_label }}</strong> telah siap digunakan.
                        </span>
                    </div>

                    <x-slot:actions separator class="items-center">
                        <x-button wire:click="continueToApp" class="btn-primary btn-wide" type="button">
                            <x-icon name="phosphor.house" class="w-5 h-5 mr-2" />
                            Masuk ke Dashboard
                        </x-button>
                    </x-slot:actions>
                </x-card>

                {{-- Success Tips Card --}}
                <x-card title="Tips Keamanan" subtitle="Jaga keamanan akun Anda">
                    <div class="space-y-2 text-sm text-base-content/70">
                        <div class="flex items-start gap-2">
                            <x-icon name="phosphor.shield-check" class="w-4 h-4 text-success mt-0.5 flex-shrink-0" />
                            <span>Email Anda telah berhasil diverifikasi dan akun Anda sekarang aman</span>
                        </div>
                        <div class="flex items-start gap-2">
                            <x-icon name="phosphor.lock" class="w-4 h-4 text-info mt-0.5 flex-shrink-0" />
                            <span>Gunakan password yang kuat dan jangan bagikan ke orang lain</span>
                        </div>
                        <div class="flex items-start gap-2">
                            <x-icon name="phosphor.sign-out" class="w-4 h-4 text-warning mt-0.5 flex-shrink-0" />
                            <span>Selalu logout ketika menggunakan komputer umum</span>
                        </div>
                    </div>
                </x-card>
            </div>
        @else
            {{-- Error State --}}
            <div class="text-center space-y-4">
                {{-- Error Icon --}}
                <div class="w-20 h-20 mx-auto mb-4 bg-error/20 rounded-full flex items-center justify-center">
                    <x-icon name="phosphor.x-circle" class="w-10 h-10 text-error" />
                </div>

                <h1 class="text-2xl font-bold text-base-content mb-2">Verifikasi Gagal</h1>
                <p class="text-base-content/70 mb-4">{{ $message }}</p>

                {{-- Error Info Card --}}
                <x-card title="Kemungkinan Penyebab" subtitle="Alasan verifikasi gagal" class="bg-base-200" >
                    <div class="space-y-2 text-sm text-base-content/80">
                        <div class="flex items-start gap-2">
                            <x-icon name="phosphor.clock" class="w-4 h-4 text-error mt-0.5 flex-shrink-0" />
                            <span>Link verifikasi sudah kadaluarsa (berlaku 60 menit)</span>
                        </div>
                        <div class="flex items-start gap-2">
                            <x-icon name="phosphor.check" class="w-4 h-4 text-error mt-0.5 flex-shrink-0" />
                            <span>Link verifikasi sudah pernah digunakan</span>
                        </div>
                        <div class="flex items-start gap-2">
                            <x-icon name="phosphor.x" class="w-4 h-4 text-error mt-0.5 flex-shrink-0" />
                            <span>Link verifikasi tidak valid atau rusak</span>
                        </div>
                        <div class="flex items-start gap-2">
                            <x-icon name="phosphor.envelope" class="w-4 h-4 text-error mt-0.5 flex-shrink-0" />
                            <span>Email sudah terverifikasi sebelumnya</span>
                        </div>
                    </div>
                </x-card>

                {{-- Action Buttons Card --}}
                <x-card>
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
                                    Minta Verifikasi Baru
                                    @if ($this->rateLimitInfo['remaining_attempts'] > 0 && $this->rateLimitInfo['remaining_attempts'] <= 2)
                                        <span class="text-xs opacity-75">({{ $this->rateLimitInfo['remaining_attempts'] }} tersisa)</span>
                                    @endif
                                </span>
                                <span wire:loading wire:target="resendVerification">
                                    <x-icon name="phosphor.spinner" class="w-5 h-5 mr-2 animate-spin" />
                                    Memproses...
                                </span>
                            </x-button>
                        @endif

                        <x-button wire:click="continueToApp" class="btn-outline" type="button">
                            <x-icon name="phosphor.house" class="w-5 h-5 mr-2" />
                            Ke Dashboard
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
            </div>
        @endif
    @else
        {{-- Loading State --}}
        <div class="text-center space-y-4">
            {{-- Loading Animation --}}
            <div class="w-20 h-20 mx-auto mb-4 bg-primary/20 rounded-full flex items-center justify-center">
                <span class="loading loading-spinner loading-lg text-primary"></span>
            </div>

            <h1 class="text-2xl font-bold text-base-content mb-2">Memverifikasi Email...</h1>
            <p class="text-base-content/70">Mohon tunggu, kami sedang memverifikasi email Anda.</p>

            {{-- Processing Card --}}
            <x-card title="Proses Verifikasi" subtitle="Sedang memproses permintaan Anda" class="bg-base-200" >
                <div class="flex items-center justify-center gap-3">
                    <span class="loading loading-dots loading-md"></span>
                    <span class="text-sm text-base-content/70">Memvalidasi link verifikasi...</span>
                </div>
            </x-card>
        </div>
    @endif

    {{-- Support Card --}}
    <x-card title="Butuh Bantuan?" subtitle="Hubungi tim support kami">
        <div class="space-y-2 text-sm text-base-content/70">
            <div class="flex items-center gap-3">
                <x-icon name="phosphor.envelope" class="w-5 h-5 text-primary flex-shrink-0" />
                <span>
                    Masih bermasalah? Hubungi administrator di
                    <a href="mailto:admin@delivtrack.test" class="link font-medium text-primary">admin@delivtrack.test</a>
                </span>
            </div>
            @if (!$verificationSuccess && $verificationProcessed)
                <div class="flex items-center gap-3">
                    <x-icon name="phosphor.info" class="w-5 h-5 text-info flex-shrink-0" />
                    <span>Sertakan pesan error: "<em>{{ $message }}</em>" ketika menghubungi support</span>
                </div>
            @endif
            @if ($this->rateLimitInfo['is_limited'])
                <div class="flex items-center gap-3">
                    <x-icon name="phosphor.shield-check" class="w-5 h-5 text-success flex-shrink-0" />
                    <span>Batas percobaan: {{ $this->rateLimitInfo['limits']['per_user'] }} kali per {{ $this->rateLimitInfo['limits']['window_minutes'] }} menit untuk melindungi akun Anda</span>
                </div>
            @endif
        </div>
    </x-card>
</div>
