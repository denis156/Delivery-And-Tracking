<div class="space-y-6">
    @if(!$linkSent)
        <div class="text-center">
            <h1 class="text-2xl font-bold text-base-content mb-2">Lupa Password?</h1>
            <p class="text-base-content/70">Masukkan email Anda untuk mendapatkan link reset password</p>
        </div>

        <x-card class="bg-base-200">
            <x-form wire:submit="sendResetLink" no-separator>
                <x-input
                    label="Email"
                    wire:model.live.debounce.500ms="email"
                    type="email"
                    placeholder="Masukkan email Anda"
                    icon="phosphor.envelope"
                    autofocus
                    required
                />

                <x-button
                    type="submit"
                    class="btn-primary w-full"
                    size="lg"
                    wire:loading.attr="disabled"
                    wire:target="sendResetLink"
                >
                    <span wire:loading.remove wire:target="sendResetLink">
                        <x-icon name="phosphor.paper-plane-tilt" class="w-5 h-5 mr-2" />
                        Kirim Link Reset Password
                    </span>
                    <span wire:loading wire:target="sendResetLink">
                        <x-icon name="phosphor.spinner" class="w-5 h-5 mr-2 animate-spin" />
                        Mengirim...
                    </span>
                </x-button>

                <div class="text-center">
                    <x-button wire:click="backToLogin" class="btn-ghost btn-sm" type="button">
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

            <h1 class="text-2xl font-bold text-base-content mb-2">Link Telah Dikirim!</h1>
            <p class="text-base-content/70 mb-4">
                Kami telah mengirimkan link reset password ke email <strong>{{ $email }}</strong>
            </p>

            <div class="flex gap-3 justify-center">
                <x-button wire:click="resendLink" class="btn-outline" type="button">
                    <x-icon name="phosphor.paper-plane-tilt" class="w-4 h-4 mr-2" />
                    Kirim Ulang
                </x-button>
                <x-button wire:click="backToLogin" class="btn-primary" type="button">
                    <x-icon name="phosphor.arrow-left" class="w-4 h-4 mr-2" />
                    Kembali ke Login
                </x-button>
            </div>
        </div>
    @endif
</div>
