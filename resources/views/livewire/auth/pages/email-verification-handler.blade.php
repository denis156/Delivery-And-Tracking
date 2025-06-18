<div class="space-y-6">
    @if ($verificationProcessed)
        @if ($verificationSuccess)
            <div class="text-center space-y-4">
                <div class="w-20 h-20 mx-auto mb-4 bg-success/20 rounded-full flex items-center justify-center">
                    <x-icon name="phosphor.check-circle" class="w-10 h-10 text-success" />
                </div>

                <h1 class="text-2xl font-bold text-base-content mb-2">Email Berhasil Diverifikasi!</h1>
                <p class="text-base-content/70">{{ $message }}</p>

                <x-card title="Selamat Datang!" subtitle="Akun Anda telah aktif" class="bg-base-200">
                    <div class="flex items-center gap-3 text-sm text-base-content/80">
                        <x-icon name="phosphor.confetti" class="w-6 h-6 text-success flex-shrink-0" />
                        <span>
                            Halo <strong>{{ auth()->user()->name }}</strong>!
                            Akun Anda sebagai <strong>{{ auth()->user()->role_label }}</strong> telah siap digunakan.
                        </span>
                    </div>

                    <x-slot:actions separator class="items-center">
                        <x-button wire:click="continueToApp" class="btn-primary btn-block" type="button">
                            <x-icon name="phosphor.house" class="w-5 h-5 mr-2" />
                            Masuk ke Dashboard
                        </x-button>
                    </x-slot:actions>
                </x-card>
            </div>
        @else
            <div class="text-center space-y-4">
                <div class="w-20 h-20 mx-auto mb-4 bg-error/20 rounded-full flex items-center justify-center">
                    <x-icon name="phosphor.x-circle" class="w-10 h-10 text-error" />
                </div>

                <h1 class="text-2xl font-bold text-base-content mb-2">Verifikasi Gagal</h1>
                <p class="text-base-content/70 mb-4">{{ $message }}</p>

                <x-card>
                    <div class="flex gap-3 justify-center">
                        <x-button wire:click="resendVerification" class="btn-primary" type="button">
                            <x-icon name="phosphor.paper-plane-tilt" class="w-5 h-5 mr-2" />
                            Minta Verifikasi Baru
                        </x-button>
                        <x-button wire:click="continueToApp" class="btn-outline" type="button">
                            <x-icon name="phosphor.house" class="w-5 h-5 mr-2" />
                            Ke Dashboard
                        </x-button>
                    </div>
                </x-card>
            </div>
        @endif
    @else
        <div class="text-center space-y-4">
            <div class="w-20 h-20 mx-auto mb-4 bg-primary/20 rounded-full flex items-center justify-center">
                <span class="loading loading-spinner loading-lg text-primary"></span>
            </div>

            <h1 class="text-2xl font-bold text-base-content mb-2">Memverifikasi Email...</h1>
            <p class="text-base-content/70">Mohon tunggu, kami sedang memverifikasi email Anda.</p>
        </div>
    @endif

    <x-card title="Butuh Bantuan?" subtitle="Hubungi tim support kami">
        <div class="text-sm text-base-content/70">
            <div class="flex items-center gap-3">
                <x-icon name="phosphor.envelope" class="w-5 h-5 text-primary flex-shrink-0" />
                <span>
                    Masih bermasalah? Hubungi administrator di
                    <a href="mailto:admin@delivtrack.test"
                        class="link font-medium text-primary">admin@delivtrack.test</a>
                </span>
            </div>
        </div>
    </x-card>
</div>
