<div class="space-y-6">
    @if(!$passwordReset)
        <div class="text-center">
            <h1 class="text-2xl font-bold text-base-content mb-2">Reset Password</h1>
            <p class="text-base-content/70">Masukkan password baru untuk akun Anda</p>
        </div>

        <x-card class="bg-base-200">
            <x-form wire:submit="resetPassword" no-separator>
                <x-input
                    label="Email"
                    wire:model="email"
                    type="email"
                    placeholder="Email Anda"
                    icon="phosphor.envelope"
                    readonly
                    required
                />

                <x-password
                    label="Password Baru"
                    wire:model="password"
                    placeholder="Masukkan password baru"
                    icon="phosphor.lock"
                    right
                    required
                    hint="Minimal 8 karakter"
                />

                <x-password
                    label="Konfirmasi Password"
                    wire:model="password_confirmation"
                    placeholder="Konfirmasi password baru"
                    icon="phosphor.lock"
                    right
                    required
                />

                <x-button
                    type="submit"
                    class="btn-primary w-full"
                    size="lg"
                    wire:loading.attr="disabled"
                    wire:target="resetPassword"
                >
                    <span wire:loading.remove wire:target="resetPassword">
                        <x-icon name="phosphor.lock" class="w-5 h-5 mr-2" />
                        Reset Password
                    </span>
                    <span wire:loading wire:target="resetPassword">
                        <x-icon name="phosphor.spinner" class="w-5 h-5 mr-2 animate-spin" />
                        Mereset Password...
                    </span>
                </x-button>

                <div class="flex gap-3 justify-center text-center">
                    <x-button wire:click="requestNewLink" class="btn-ghost btn-sm" type="button">
                        <x-icon name="phosphor.paper-plane-tilt" class="w-4 h-4 mr-1" />
                        Minta Link Baru
                    </x-button>
                    <x-button wire:click="goToLogin" class="btn-ghost btn-sm" type="button">
                        <x-icon name="phosphor.arrow-left" class="w-4 h-4 mr-1" />
                        Kembali ke Login
                    </x-button>
                </div>
            </x-form>
        </x-card>
    @else
        <div class="text-center space-y-4">
            <div class="w-20 h-20 mx-auto mb-4 bg-success/20 rounded-full flex items-center justify-center">
                <x-icon name="phosphor.check-circle" class="w-10 h-10 text-success" />
            </div>

            <h1 class="text-2xl font-bold text-base-content mb-2">Password Berhasil Direset!</h1>
            <p class="text-base-content/70 mb-4">
                Password Anda telah berhasil diubah. Sekarang Anda dapat login dengan password baru.
            </p>

            <x-button wire:click="goToLogin" class="btn-primary btn-wide" type="button">
                <x-icon name="phosphor.sign-in" class="w-5 h-5 mr-2" />
                Login Sekarang
            </x-button>
        </div>
    @endif
</div>
