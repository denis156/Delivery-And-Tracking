<nav
    class="navbar w-full bg-base-100/85 backdrop-blur-md shadow-xl border-b border-base-content/10 sticky top-0 z-50 transition-all duration-300">
    <div class="navbar-start">
        <!-- Mobile menu button -->
        <div class="dropdown lg:hidden">
            <div tabindex="0" role="button"
                class="btn btn-ghost btn-circle hover:bg-primary/10 transition-all duration-300 group">
                <x-icon name="phosphor.list" class="w-6 h-6 group-hover:scale-110 transition-transform" />
            </div>

            <ul tabindex="0"
                class="menu menu-sm dropdown-content mt-3 z-[1] p-3 shadow-2xl bg-base-100/95 backdrop-blur-md rounded-2xl border border-base-content/10 w-64">
                <li class="menu-title">
                    <span
                        class="text-primary font-semibold text-xs uppercase tracking-wider opacity-70 flex items-center">
                        <x-icon name="phosphor.navigation-arrow" class="w-3 h-3 mr-1" />
                        Navigation
                    </span>
                </li>
                <div class="divider my-2"></div>
                <li>
                    <a href="#home"
                        class="group flex items-center space-x-3 rounded-xl hover:bg-gradient-to-r hover:from-primary/10 hover:to-primary/5 transition-all duration-300">
                        <div
                            class="w-8 h-8 bg-primary/10 rounded-lg flex items-center justify-center group-hover:bg-primary/20 transition-colors">
                            <x-icon name="phosphor.house" class="w-4 h-4 text-primary" />
                        </div>
                        <span class="font-medium">Beranda</span>
                    </a>
                </li>
                <li>
                    <a href="#features"
                        class="group flex items-center space-x-3 hover:bg-gradient-to-r hover:from-secondary/10 hover:to-secondary/5 transition-all duration-300">
                        <div
                            class="w-8 h-8 bg-secondary/10 rounded-lg flex items-center justify-center group-hover:bg-secondary/20 transition-colors">
                            <x-icon name="phosphor.star" class="w-4 h-4 text-secondary" />
                        </div>
                        <span class="font-medium">Fitur</span>
                    </a>
                </li>
                <li>
                    <a href="#how-it-works"
                        class="group flex items-center space-x-3 rounded-xl hover:bg-gradient-to-r hover:from-accent/10 hover:to-accent/5 transition-all duration-300">
                        <div
                            class="w-8 h-8 bg-accent/10 rounded-lg flex items-center justify-center group-hover:bg-accent/20 transition-colors">
                            <x-icon name="phosphor.gear" class="w-4 h-4 text-accent" />
                        </div>
                        <span class="font-medium">Cara Kerja</span>
                    </a>
                </li>
                <li>
                    <a href="#benefits"
                        class="group flex items-center space-x-3 rounded-xl hover:bg-gradient-to-r hover:from-info/10 hover:to-info/5 transition-all duration-300">
                        <div
                            class="w-8 h-8 bg-info/10 rounded-lg flex items-center justify-center group-hover:bg-info/20 transition-colors">
                            <x-icon name="phosphor.trophy" class="w-4 h-4 text-info" />
                        </div>
                        <span class="font-medium">Keunggulan</span>
                    </a>
                </li>
                <li>
                    <a href="#contact"
                        class="group flex items-center space-x-3 rounded-xl hover:bg-gradient-to-r hover:from-success/10 hover:to-success/5 transition-all duration-300">
                        <div
                            class="w-8 h-8 bg-success/10 rounded-lg flex items-center justify-center group-hover:bg-success/20 transition-colors">
                            <x-icon name="phosphor.phone" class="w-4 h-4 text-success" />
                        </div>
                        <span class="font-medium">Kontak</span>
                    </a>
                </li>
            </ul>
        </div>

        <!-- Enhanced Logo -->
        <a href="#home" class="group hidden sm:flex items-center space-x-3 ml-2 lg:ml-0">
            <div class="relative">
                <!-- Glow effect behind logo -->
                <div
                    class="absolute -inset-1 bg-gradient-to-r from-primary/30 to-secondary/30 rounded-xl blur opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                </div>

                <!-- Logo container -->
                <div
                    class="relative w-12 h-12 bg-gradient-to-br from-primary/20 via-secondary/10 to-accent/20 rounded-xl flex items-center justify-center border border-primary/20 group-hover:scale-110 group-hover:rotate-3 transition-all duration-300">
                    <x-icon name="phosphor.shipping-container"
                        class="w-7 h-7 text-primary group-hover:text-secondary transition-colors duration-300" />
                </div>
            </div>

            <div class="flex flex-col">
                <div
                    class="font-bold text-xl bg-gradient-to-r from-primary via-secondary to-accent bg-clip-text text-transparent">
                    {{ config('app.name', 'BKM') }}
                </div>
                <div class="text-xs text-base-content/60 -mt-1 font-medium">
                    {{ config('landingpage.company_tagline', 'Transport System') }}</div>
            </div>
        </a>
    </div>

    <div class="navbar-center hidden lg:flex">
        <ul class="menu menu-horizontal px-1 space-x-1">
            <li>
                <a href="#home"
                    class="group relative px-4 py-2 text-base-content hover:text-primary transition-colors duration-300 font-medium hover:bg-transparent">
                    <span class="relative z-10">Beranda</span>
                    <div
                        class="absolute bottom-0 left-1/2 w-0 h-0.5 bg-gradient-to-r from-primary to-secondary group-hover:w-full group-hover:left-0 transition-all duration-300">
                    </div>
                </a>
            </li>
            <li>
                <a href="#features"
                    class="group relative px-4 py-2 text-base-content hover:text-secondary transition-colors duration-300 font-medium hover:bg-transparent">
                    <span class="relative z-10">Fitur</span>
                    <div
                        class="absolute bottom-0 left-1/2 w-0 h-0.5 bg-gradient-to-r from-secondary to-accent group-hover:w-full group-hover:left-0 transition-all duration-300">
                    </div>
                </a>
            </li>
            <li>
                <a href="#how-it-works"
                    class="group relative px-4 py-2 text-base-content hover:text-accent transition-colors duration-300 font-medium hover:bg-transparent">
                    <span class="relative z-10">Cara Kerja</span>
                    <div
                        class="absolute bottom-0 left-1/2 w-0 h-0.5 bg-gradient-to-r from-accent to-info group-hover:w-full group-hover:left-0 transition-all duration-300">
                    </div>
                </a>
            </li>
            <li>
                <a href="#benefits"
                    class="group relative px-4 py-2 text-base-content hover:text-info transition-colors duration-300 font-medium hover:bg-transparent">
                    <span class="relative z-10">Keunggulan</span>
                    <div
                        class="absolute bottom-0 left-1/2 w-0 h-0.5 bg-gradient-to-r from-info to-success group-hover:w-full group-hover:left-0 transition-all duration-300">
                    </div>
                </a>
            </li>
            <li>
                <a href="#contact"
                    class="group relative px-4 py-2 text-base-content hover:text-success transition-colors duration-300 font-medium hover:bg-transparent">
                    <span class="relative z-10">Kontak</span>
                    <div
                        class="absolute bottom-0 left-1/2 w-0 h-0.5 bg-gradient-to-r from-success to-primary group-hover:w-full group-hover:left-0 transition-all duration-300">
                    </div>
                </a>
            </li>
        </ul>
    </div>

    <div class="navbar-end">
        <div class="flex items-center space-x-3">
            <!-- Driver Button -->
            <x-button link="{{ route('driver.dashboard') }}" class="btn-info btn-sm btn-outline" label="Portal Pengemudi"
                icon="phosphor.truck-trailer" responsive />

            <!-- Management App Button -->
            <x-button link="{{ route('app.dashboard') }}" wire:navigate.hover class="btn-primary btn-sm"
                label="Masuk Ke Dashboard" icon="phosphor.gauge" responsive />
        </div>
    </div>
</nav>
