<footer class="relative bg-gradient-to-br from-base-200 via-base-300 to-base-200 text-base-content overflow-hidden">
    <!-- Background Effects -->
    <div class="absolute inset-0 bg-grid-pattern opacity-5"></div>
    <div class="absolute top-0 left-0 w-96 h-96 bg-primary/10 rounded-full blur-3xl -translate-x-1/2 -translate-y-1/2">
    </div>
    <div
        class="absolute bottom-0 right-0 w-96 h-96 bg-secondary/10 rounded-full blur-3xl translate-x-1/2 translate-y-1/2">
    </div>

    <!-- Main Footer Content -->
    <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-12 mb-12">

            <!-- Company Info -->
            <div class="scroll-animate fade-in-left">
                <div class="group relative">
                    <!-- Company Header -->
                    <div class="flex items-center space-x-4 mb-6">
                        <div class="relative">
                            <div
                                class="absolute -inset-1 bg-gradient-to-r from-primary to-secondary rounded-2xl blur opacity-20 group-hover:opacity-40 transition-opacity duration-300">
                            </div>
                            <div
                                class="relative w-14 h-14 bg-gradient-to-br from-primary/20 to-secondary/20 rounded-2xl flex items-center justify-center">
                                <x-icon name="phosphor.shipping-container" class="w-8 h-8 text-primary" />
                            </div>
                        </div>
                        <div>
                            <h3
                                class="font-bold text-2xl bg-gradient-to-r from-primary to-secondary bg-clip-text text-transparent">
                                {{ config('landingpage.company_name') }}
                            </h3>
                            <p class="text-sm text-base-content/70">{{ config('landingpage.company_tagline') }}</p>
                        </div>
                    </div>

                    <p class="text-base-content/80 leading-relaxed mb-6">
                        Solusi terpercaya untuk manajemen transportasi dengan sistem tracking real-time dan workflow
                        terintegrasi untuk efisiensi operasional maksimal.
                    </p>

                    <!-- Social Media Links -->
                    <div class="flex space-x-3">
                        <a href="#" class="group relative w-10 h-10">
                            <div
                                class="absolute inset-0 bg-gradient-to-r from-blue-500/20 to-blue-600/20 rounded-xl blur opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                            </div>
                            <div
                                class="relative w-10 h-10 bg-base-100/80 backdrop-blur border border-base-content/10 rounded-xl flex items-center justify-center hover:bg-blue-50 hover:border-blue-200 transition-all duration-300 group-hover:scale-110">
                                <x-icon name="phosphor.facebook-logo" class="w-5 h-5 text-blue-600" />
                            </div>
                        </a>
                        <a href="#" class="group relative w-10 h-10">
                            <div
                                class="absolute inset-0 bg-gradient-to-r from-sky-500/20 to-sky-600/20 rounded-xl blur opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                            </div>
                            <div
                                class="relative w-10 h-10 bg-base-100/80 backdrop-blur border border-base-content/10 rounded-xl flex items-center justify-center hover:bg-sky-50 hover:border-sky-200 transition-all duration-300 group-hover:scale-110">
                                <x-icon name="phosphor.twitter-logo" class="w-5 h-5 text-sky-600" />
                            </div>
                        </a>
                        <a href="#" class="group relative w-10 h-10">
                            <div
                                class="absolute inset-0 bg-gradient-to-r from-blue-700/20 to-blue-800/20 rounded-xl blur opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                            </div>
                            <div
                                class="relative w-10 h-10 bg-base-100/80 backdrop-blur border border-base-content/10 rounded-xl flex items-center justify-center hover:bg-blue-50 hover:border-blue-200 transition-all duration-300 group-hover:scale-110">
                                <x-icon name="phosphor.linkedin-logo" class="w-5 h-5 text-blue-700" />
                            </div>
                        </a>
                        <a href="https://github.com/denis156" target="_blank" class="group relative w-10 h-10">
                            <div
                                class="absolute inset-0 bg-gradient-to-r from-gray-700/20 to-gray-800/20 rounded-xl blur opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                            </div>
                            <div
                                class="relative w-10 h-10 bg-base-100/80 backdrop-blur border border-base-content/10 rounded-xl flex items-center justify-center hover:bg-gray-50 hover:border-gray-200 transition-all duration-300 group-hover:scale-110">
                                <x-icon name="phosphor.github-logo" class="w-5 h-5 text-gray-700" />
                            </div>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Quick Links -->
            <div class="scroll-animate scale-in">
                <div class="group">
                    <div class="flex items-center space-x-3 mb-6">
                        <div class="w-8 h-8 bg-secondary/10 rounded-xl flex items-center justify-center">
                            <x-icon name="phosphor.link" class="w-5 h-5 text-secondary" />
                        </div>
                        <h4 class="font-bold text-xl text-secondary">Tautan Cepat</h4>
                    </div>

                    <ul class="space-y-3">
                        <li>
                            <a href="#home"
                                class="group flex items-center space-x-3 text-base-content/70 hover:text-primary transition-all duration-300 hover:translate-x-2">
                                <x-icon name="phosphor.house"
                                    class="w-4 h-4 group-hover:scale-110 transition-transform" />
                                <span>Beranda</span>
                            </a>
                        </li>
                        <li>
                            <a href="#features"
                                class="group flex items-center space-x-3 text-base-content/70 hover:text-primary transition-all duration-300 hover:translate-x-2">
                                <x-icon name="phosphor.star"
                                    class="w-4 h-4 group-hover:scale-110 transition-transform" />
                                <span>Fitur Sistem</span>
                            </a>
                        </li>
                        <li>
                            <a href="#how-it-works"
                                class="group flex items-center space-x-3 text-base-content/70 hover:text-primary transition-all duration-300 hover:translate-x-2">
                                <x-icon name="phosphor.gear"
                                    class="w-4 h-4 group-hover:scale-110 transition-transform" />
                                <span>Cara Kerja</span>
                            </a>
                        </li>
                        <li>
                            <a href="#benefits"
                                class="group flex items-center space-x-3 text-base-content/70 hover:text-primary transition-all duration-300 hover:translate-x-2">
                                <x-icon name="phosphor.trophy"
                                    class="w-4 h-4 group-hover:scale-110 transition-transform" />
                                <span>Keunggulan</span>
                            </a>
                        </li>
                        <li>
                            <a href="#contact"
                                class="group flex items-center space-x-3 text-base-content/70 hover:text-primary transition-all duration-300 hover:translate-x-2">
                                <x-icon name="phosphor.phone"
                                    class="w-4 h-4 group-hover:scale-110 transition-transform" />
                                <span>Kontak</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Services -->
            <div class="scroll-animate fade-in-right">
                <div class="group">
                    <div class="flex items-center space-x-3 mb-6">
                        <div class="w-8 h-8 bg-accent/10 rounded-xl flex items-center justify-center">
                            <x-icon name="phosphor.gear-six" class="w-5 h-5 text-accent" />
                        </div>
                        <h4 class="font-bold text-xl text-accent">Layanan</h4>
                    </div>

                    <ul class="space-y-3">
                        <li class="flex items-center space-x-3 text-base-content/70">
                            <div class="w-2 h-2 bg-primary rounded-full"></div>
                            <span>Real-time Tracking</span>
                        </li>
                        <li class="flex items-center space-x-3 text-base-content/70">
                            <div class="w-2 h-2 bg-secondary rounded-full"></div>
                            <span>Digital Surat Jalan</span>
                        </li>
                        <li class="flex items-center space-x-3 text-base-content/70">
                            <div class="w-2 h-2 bg-accent rounded-full"></div>
                            <span>Multi-role Access</span>
                        </li>
                        <li class="flex items-center space-x-3 text-base-content/70">
                            <div class="w-2 h-2 bg-info rounded-full"></div>
                            <span>Workflow Management</span>
                        </li>
                        <li class="flex items-center space-x-3 text-base-content/70">
                            <div class="w-2 h-2 bg-success rounded-full"></div>
                            <span>Reporting & Analytics</span>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Contact Info -->
            <div class="scroll-animate fade-in-up">
                <div class="group">
                    <div class="flex items-center space-x-3 mb-6">
                        <div class="w-8 h-8 bg-info/10 rounded-xl flex items-center justify-center">
                            <x-icon name="phosphor.address-book" class="w-5 h-5 text-info" />
                        </div>
                        <h4 class="font-bold text-xl text-info">Kontak</h4>
                    </div>

                    <div class="space-y-4">
                        <!-- Address -->
                        <div class="group">
                            <div
                                class="flex items-start space-x-3 p-3 rounded-xl hover:bg-base-100/50 transition-colors duration-300">
                                <div
                                    class="w-6 h-6 bg-primary/10 rounded-lg flex items-center justify-center flex-shrink-0 mt-0.5">
                                    <x-icon name="phosphor.map-pin" class="w-4 h-4 text-primary" />
                                </div>
                                <div class="text-sm">
                                    <p class="text-base-content font-medium mb-1">Alamat Kantor</p>
                                    <p class="text-base-content/70 leading-relaxed">
                                        {{ config('landingpage.contact.address.street', 'Jl. Raya No. 123') }}<br>
                                        {{ config('landingpage.contact.address.city', 'Balikpapan') }},
                                        {{ config('landingpage.contact.address.province', 'Kalimantan Timur') }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Phone -->
                        <div class="group">
                            <a href="tel:{{ config('landingpage.contact.phone.primary', '+62 542 123 4567') }}"
                                class="flex items-center space-x-3 p-3 rounded-xl hover:bg-base-100/50 transition-all duration-300 hover:scale-105">
                                <div
                                    class="w-6 h-6 bg-secondary/10 rounded-lg flex items-center justify-center flex-shrink-0">
                                    <x-icon name="phosphor.phone" class="w-4 h-4 text-secondary" />
                                </div>
                                <div class="text-sm">
                                    <p class="text-base-content font-medium mb-1">Telepon</p>
                                    <p class="text-secondary font-semibold">
                                        {{ config('landingpage.contact.phone.primary', '+62 542 123 4567') }}</p>
                                </div>
                            </a>
                        </div>

                        <!-- Email -->
                        <div class="group">
                            <a href="mailto:{{ config('landingpage.contact.email.info', 'info@bkm-transport.co.id') }}"
                                class="flex items-center space-x-3 p-3 rounded-xl hover:bg-base-100/50 transition-all duration-300 hover:scale-105">
                                <div
                                    class="w-6 h-6 bg-accent/10 rounded-lg flex items-center justify-center flex-shrink-0">
                                    <x-icon name="phosphor.envelope" class="w-4 h-4 text-accent" />
                                </div>
                                <div class="text-sm">
                                    <p class="text-base-content font-medium mb-1">Email</p>
                                    <p class="text-accent font-semibold">
                                        {{ config('landingpage.contact.email.info', 'info@bkm-transport.co.id') }}</p>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistics Bar -->
        <div class="scroll-animate scale-in mb-12">
            <div class="relative group">
                <div
                    class="absolute -inset-1 bg-gradient-to-r from-primary to-secondary rounded-2xl blur opacity-20 group-hover:opacity-30 transition-opacity duration-300">
                </div>

                <x-card class="relative bg-base-100/80 backdrop-blur border border-base-content/10 rounded-2xl p-6">
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-6 text-center">
                        <div class="group">
                            <div
                                class="w-12 h-12 bg-primary/10 rounded-xl flex items-center justify-center mx-auto mb-2 group-hover:scale-110 transition-transform">
                                <x-icon name="phosphor.users-four" class="w-6 h-6 text-primary" />
                            </div>
                            <div class="text-2xl font-bold text-primary mb-1">
                                {{ config('landingpage.stats.clients_count', '100+') }}</div>
                            <div class="text-sm text-base-content/60">Klien Terpercaya</div>
                        </div>

                        <div class="group">
                            <div
                                class="w-12 h-12 bg-secondary/10 rounded-xl flex items-center justify-center mx-auto mb-2 group-hover:scale-110 transition-transform">
                                <x-icon name="phosphor.files" class="w-6 h-6 text-secondary" />
                            </div>
                            <div class="text-2xl font-bold text-secondary mb-1">
                                {{ config('landingpage.stats.documents_processed', '50K+') }}</div>
                            <div class="text-sm text-base-content/60">Surat Jalan</div>
                        </div>

                        <div class="group">
                            <div
                                class="w-12 h-12 bg-accent/10 rounded-xl flex items-center justify-center mx-auto mb-2 group-hover:scale-110 transition-transform">
                                <x-icon name="phosphor.clock-user" class="w-6 h-6 text-accent" />
                            </div>
                            <div class="text-2xl font-bold text-accent mb-1">
                                {{ config('landingpage.stats.uptime', '99.9%') }}</div>
                            <div class="text-sm text-base-content/60">Uptime</div>
                        </div>

                        <div class="group">
                            <div
                                class="w-12 h-12 bg-info/10 rounded-xl flex items-center justify-center mx-auto mb-2 group-hover:scale-110 transition-transform">
                                <x-icon name="phosphor.calendar-star" class="w-6 h-6 text-info" />
                            </div>
                            <div class="text-2xl font-bold text-info mb-1">
                                {{ config('landingpage.stats.years_experience', '5+') }}</div>
                            <div class="text-sm text-base-content/60">Tahun</div>
                        </div>
                    </div>
                </x-card>
            </div>
        </div>
    </div>

    <!-- Developer Credits & Copyright -->
    <div class="relative z-10 border-t border-base-content/10 bg-base-100/30 backdrop-blur">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="scroll-animate fade-in-up">
                <div class="flex flex-col lg:flex-row justify-between items-center space-y-6 lg:space-y-0">
                    <!-- Copyright -->
                    <div class="text-center lg:text-left">
                        <p class="text-base-content/70 mb-2">
                            &copy; {{ date('Y') }}
                            {{ config('landingpage.company_name', 'PT. Barraka Karya Mandiri') }}.
                            Semua hak cipta dilindungi.
                        </p>
                        <p class="text-sm text-base-content/50">
                            Sistem Manajemen Transportasi Terintegrasi
                        </p>
                    </div>

                    <!-- Developer Credits -->
                    <div class="text-center lg:text-left">
                        <p class="text-sm text-base-content/60 mb-3">Dikembangkan dengan ❤️ oleh :</p>

                        <div class="group relative">
                            <div
                                class="absolute -inset-1 bg-gradient-to-r from-accent to-primary rounded-2xl blur opacity-20 group-hover:opacity-40 transition-opacity duration-300">
                            </div>

                            <x-card
                                class="relative bg-gradient-to-r from-accent/90 to-primary/90 backdrop-blur text-primary-content rounded-2xl p-4">
                                <div
                                    class="flex flex-col sm:flex-row items-center space-y-3 sm:space-y-0 sm:space-x-4">
                                    <div class="relative">
                                        <x-avatar :image="'https://avatars.githubusercontent.com/u/56766058?v=4'" alt="Denis Djodian Ardika"
                                            class="relative h-18 w-18 ring-2 ring-primary-content/30">
                                            <x-slot:title class="mb-1">
                                                <div class="flex flex-col items-start">
                                                    <h4 class="font-extrabold text-md text-base-100">
                                                        {{ config('landingpage.developer.name') }}</h4>
                                                    <p class="font-light text-sm text-base-300">
                                                        {{ config('landingpage.developer.role') }}</p>
                                                </div>
                                            </x-slot:title>
                                            <x-slot:subtitle>
                                                <div class="grid grid-cols-2 gap-2 items-start">
                                                    <x-button
                                                        link="{{ config('landingpage.developer.website', 'https://arteliadev.cloud') }}"
                                                        external icon="phosphor.globe"
                                                        class="btn-base-content btn-sm btn-base-content"
                                                        label="{{ config('landingpage.developer.company', 'Artelia.dev') }}"
                                                        />
                                                    <x-button
                                                        link="{{ config('landingpage.developer.github', 'https://github.com/denis156') }}"
                                                        external icon="si.github"
                                                        class="btn-base-content btn-sm btn-base-content" label="GitHub"
                                                        />
                                                </div>
                                            </x-slot:subtitle>
                                        </x-avatar>
                                    </div>
                                </div>
                            </x-card>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Floating Elements -->
    <div class="absolute top-32 right-10 w-4 h-4 bg-primary/20 rounded-full animate-pulse hidden lg:block"></div>
    <div class="absolute top-96 left-20 w-6 h-6 bg-secondary/20 rounded-full animate-pulse delay-1000 hidden lg:block">
    </div>
    <div
        class="absolute bottom-40 right-20 w-3 h-3 bg-accent/20 rounded-full animate-pulse delay-2000 hidden lg:block">
    </div>
</footer>
