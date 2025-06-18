<div class="space-y-6">
    @if (!$emailVerified)
        <div class="text-center">
            <div class="w-20 h-20 mx-auto mb-4 bg-warning/20 rounded-full flex items-center justify-center">
                <x-icon name="phosphor.envelope-simple" class="w-10 h-10 text-warning" />
            </div>

            <h1 class="text-2xl font-bold text-base-content mb-2">Verifikasi Email Anda</h1>
            <p class="text-base-content/70">
                Kami telah mengirimkan email verifikasi ke <strong>{{ auth()->user()->email }}</strong>
            </p>
        </div>

        <x-card title="Langkah Verifikasi" subtitle="Ikuti petunjuk berikut" class="bg-base-200">
            <div class="space-y-3 text-sm text-base-content/80">
                <div class="flex items-center gap-3">
                    <span class="w-6 h-6 bg-primary text-primary-content rounded-full flex items-center justify-center text-xs font-bold">1</span>
                    <span>Periksa kotak masuk email Anda</span>
                </div>
                <div class="flex items-center gap-3">
                    <span class="w-6 h-6 bg-primary text-primary-content rounded-full flex items-center justify-center text-xs font-bold">2</span>
                    <span>Klik link verifikasi yang ada di email</span>
                </div>
                <div class="flex items-center gap-3">
                    <span class="w-6 h-6 bg-primary text-primary-content rounded-full flex items-center justify-center text-xs font-bold">3</span>
                    <span>Anda akan otomatis diarahkan ke dashboard</span>
                </div>
            </div>
        </x-card>

        <x-card class="bg-base-200">
            <div class="flex gap-3 justify-center">
                <x-button wire:click="resendVerification" class="btn-primary" type="button">
                    <x-icon name="phosphor.paper-plane-tilt" class="w-5 h-5 mr-2" />
                    Kirim Ulang Email
                </x-button>
                <x-button wire:click="logout" class="btn-outline" type="button">
                    <x-icon name="phosphor.sign-out" class="w-5 h-5 mr-2" />
                    Logout
                </x-button>
            </div>
        </x-card>
    @else
        <div class="text-center space-y-4">
            <div class="w-20 h-20 mx-auto mb-4 bg-success/20 rounded-full flex items-center justify-center">
                <x-icon name="phosphor.check-circle" class="w-10 h-10 text-success" />
            </div>

            <h1 class="text-2xl font-bold text-base-content mb-2">Email Berhasil Diverifikasi!</h1>
            <p class="text-base-content/70 mb-4">
                Selamat! Email Anda telah berhasil diverifikasi. Sekarang Anda dapat mengakses semua fitur aplikasi.
            </p>

            <x-button wire:click="continueToApp" class="btn-primary btn-wide" type="button">
                <x-icon name="phosphor.house" class="w-5 h-5 mr-2" />
                Masuk ke Dashboard
            </x-button>
        </div>
    @endif
</div>
