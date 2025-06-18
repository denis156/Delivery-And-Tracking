<div class="space-y-6">
    <div class="text-center">
        <h1 class="text-2xl font-bold text-base-content mb-2">Masuk ke {{ config('app.name') }}</h1>
        <p class="text-base-content/70">Gunakan akun Anda untuk mengakses sistem</p>
    </div>

    <x-card class="bg-base-200">
        <x-form wire:submit="login" no-separator>
            <x-input label="Email" wire:model.live.debounce.500ms="email" type="email" placeholder="Masukan email"
                icon="phosphor.envelope" autofocus required />

            <x-password label="Password" wire:model="password" placeholder="Masukkan password" icon="phosphor.lock"
                right required />

            <div class="flex items-center justify-between">
                <x-checkbox wire:model="remember" label="Ingat saya" class="checkbox-primary" />
                <a href="{{ route('password.request') }}" class="text-sm text-primary hover:underline" wire:navigate>
                    Lupa password?
                </a>
            </div>

            <x-button type="submit" class="btn-primary w-full" size="lg" wire:loading.attr="disabled"
                wire:target="login">
                <span wire:loading.remove wire:target="login">
                    <x-icon name="phosphor.sign-in" class="w-5 h-5 mr-2" />
                    Masuk ke Sistem
                </span>
                <span wire:loading wire:target="login">
                    <x-icon name="phosphor.spinner" class="w-5 h-5 mr-2 animate-spin" />
                    Memproses...
                </span>
            </x-button>
        </x-form>
    </x-card>

    <div class="text-center text-xs text-base-content/60">
        Butuh bantuan? Hubungi administrator di
        <a href="mailto:admin@delivtrack.test" class="link font-medium">admin@delivtrack.test</a>
    </div>
</div>
