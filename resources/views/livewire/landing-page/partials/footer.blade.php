<footer class="relative bg-gradient-to-br from-base-200 via-base-300 to-base-200 text-base-content overflow-hidden">
    <!-- Background Effects -->
    <div class="absolute inset-0 bg-grid-pattern opacity-5"></div>
    <div class="absolute top-0 left-0 w-96 h-96 bg-primary/10 rounded-full blur-3xl -translate-x-1/2 -translate-y-1/2"></div>
    <div class="absolute bottom-0 right-0 w-96 h-96 bg-secondary/10 rounded-full blur-3xl translate-x-1/2 translate-y-1/2"></div>

    <!-- Main Footer Content -->
    <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-12 mb-12">

            <!-- Company Info -->
            <div class="scroll-animate fade-in-left">
                <div class="group relative">
                    <!-- Company Header -->
                    <div class="flex items-center space-x-4 mb-6">
                        <div class="relative">
                            <div class="absolute -inset-1 bg-gradient-to-r from-primary to-secondary rounded-2xl blur opacity-20 group-hover:opacity-40 transition-opacity duration-300"></div>
                            <div class="relative w-14 h-14 bg-gradient-to-br from-primary/20 to-secondary/20 rounded-2xl flex items-center justify-center">
                                <x-icon name="phosphor.shipping-container" class="w-8 h-8 text-primary" />
                            </div>
                        </div>
                        <div>
                            <h3 class="font-bold text-2xl bg-gradient-to-r from-primary to-secondary bg-clip-text text-transparent">
                                {{ config('landingpage.company_short') }}
                            </h3>
                            <p class="text-sm text-base-content/70">{{ config('landingpage.company_tagline') }}</p>
                        </div>
                    </div>

                    <p class="text-base-content/80 leading-relaxed mb-6">
                        {{ config('landingpage.company_description') }} melayani wilayah {{ config('landingpage.business.primary_location') }} dengan pengalaman {{ config('landingpage.stats.years_experience') }} tahun.
                    </p>

                    <!-- Social Media Links -->
                    <div class="flex space-x-3">
                        @if(config('landingpage.social.facebook'))
                        <a href="{{ config('landingpage.social.facebook') }}" target="_blank" class="group relative w-10 h-10">
                            <div class="absolute inset-0 bg-gradient-to-r from-blue-500/20 to-blue-600/20 rounded-xl blur opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                            <div class="relative w-10 h-10 bg-base-100/80 backdrop-blur border border-base-content/10 rounded-xl flex items-center justify-center hover:bg-blue-50 hover:border-blue-200 transition-all duration-300 group-hover:scale-110">
                                <x-icon name="phosphor.facebook-logo" class="w-5 h-5 text-blue-600" />
                            </div>
                        </a>
                        @endif

                        @if(config('landingpage.social.twitter'))
                        <a href="{{ config('landingpage.social.twitter') }}" target="_blank" class="group relative w-10 h-10">
                            <div class="absolute inset-0 bg-gradient-to-r from-sky-500/20 to-sky-600/20 rounded-xl blur opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                            <div class="relative w-10 h-10 bg-base-100/80 backdrop-blur border border-base-content/10 rounded-xl flex items-center justify-center hover:bg-sky-50 hover:border-sky-200 transition-all duration-300 group-hover:scale-110">
                                <x-icon name="phosphor.twitter-logo" class="w-5 h-5 text-sky-600" />
                            </div>
                        </a>
                        @endif

                        @if(config('landingpage.social.linkedin'))
                        <a href="{{ config('landingpage.social.linkedin') }}" target="_blank" class="group relative w-10 h-10">
                            <div class="absolute inset-0 bg-gradient-to-r from-blue-700/20 to-blue-800/20 rounded-xl blur opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                            <div class="relative w-10 h-10 bg-base-100/80 backdrop-blur border border-base-content/10 rounded-xl flex items-center justify-center hover:bg-blue-50 hover:border-blue-200 transition-all duration-300 group-hover:scale-110">
                                <x-icon name="phosphor.linkedin-logo" class="w-5 h-5 text-blue-700" />
                            </div>
                        </a>
                        @endif

                        @if(config('landingpage.social.instagram'))
                        <a href="{{ config('landingpage.social.instagram') }}" target="_blank" class="group relative w-10 h-10">
                            <div class="absolute inset-0 bg-gradient-to-r from-pink-500/20 to-purple-600/20 rounded-xl blur opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                            <div class="relative w-10 h-10 bg-base-100/80 backdrop-blur border border-base-content/10 rounded-xl flex items-center justify-center hover:bg-gradient-to-r hover:from-pink-50 hover:to-purple-50 hover:border-pink-200 transition-all duration-300 group-hover:scale-110">
                                <x-icon name="phosphor.instagram-logo" class="w-5 h-5 text-pink-600" />
                            </div>
                        </a>
                        @endif

                        @if(config('landingpage.developer.github'))
                        <a href="{{ config('landingpage.developer.github') }}" target="_blank" class="group relative w-10 h-10">
                            <div class="absolute inset-0 bg-gradient-to-r from-gray-700/20 to-gray-800/20 rounded-xl blur opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                            <div class="relative w-10 h-10 bg-base-100/80 backdrop-blur border border-base-content/10 rounded-xl flex items-center justify-center hover:bg-gray-50 hover:border-gray-200 transition-all duration-300 group-hover:scale-110">
                                <x-icon name="phosphor.github-logo" class="w-5 h-5 text-gray-700" />
                            </div>
                        </a>
                        @endif
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
                            <a href="#home" class="group flex items-center space-x-3 text-base-content/70 hover:text-primary transition-all duration-300 hover:translate-x-2">
                                <x-icon name="phosphor.house" class="w-4 h-4 group-hover:scale-110 transition-transform" />
                                <span>Beranda</span>
                            </a>
                        </li>
                        <li>
                            <a href="#features" class="group flex items-center space-x-3 text-base-content/70 hover:text-primary transition-all duration-300 hover:translate-x-2">
                                <x-icon name="phosphor.star" class="w-4 h-4 group-hover:scale-110 transition-transform" />
                                <span>Fitur Sistem</span>
                            </a>
                        </li>
                        <li>
                            <a href="#how-it-works" class="group flex items-center space-x-3 text-base-content/70 hover:text-primary transition-all duration-300 hover:translate-x-2">
                                <x-icon name="phosphor.gear" class="w-4 h-4 group-hover:scale-110 transition-transform" />
                                <span>Cara Kerja</span>
                            </a>
                        </li>
                        <li>
                            <a href="#benefits" class="group flex items-center space-x-3 text-base-content/70 hover:text-primary transition-all duration-300 hover:translate-x-2">
                                <x-icon name="phosphor.trophy" class="w-4 h-4 group-hover:scale-110 transition-transform" />
                                <span>Keunggulan</span>
                            </a>
                        </li>
                        <li>
                            <a href="#contact" class="group flex items-center space-x-3 text-base-content/70 hover:text-primary transition-all duration-300 hover:translate-x-2">
                                <x-icon name="phosphor.phone" class="w-4 h-4 group-hover:scale-110 transition-transform" />
                                <span>Kontak</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Services & Specializations -->
            <div class="scroll-animate fade-in-right">
                <div class="group">
                    <div class="flex items-center space-x-3 mb-6">
                        <div class="w-8 h-8 bg-accent/10 rounded-xl flex items-center justify-center">
                            <x-icon name="phosphor.gear-six" class="w-5 h-5 text-accent" />
                        </div>
                        <h4 class="font-bold text-xl text-accent">Layanan Kami</h4>
                    </div>

                    <ul class="space-y-3">
                        @php
                            $services = array_merge(
                                array_values(config('landingpage.business.services', [])),
                                array_values(config('landingpage.business.specializations', []))
                            );
                            $colors = ['primary', 'secondary', 'accent', 'info', 'success', 'warning'];
                        @endphp

                        @foreach(array_slice($services, 0, 6) as $index => $service)
                        <li class="flex items-center space-x-3 text-base-content/70">
                            <div class="w-2 h-2 bg-{{ $colors[$index % count($colors)] }} rounded-full"></div>
                            <span>{{ $service }}</span>
                        </li>
                        @endforeach
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
                        <h4 class="font-bold text-xl text-info">Kontak Kami</h4>
                    </div>

                    <div class="space-y-3">
                        <!-- Primary Contact -->
                        <div class="bg-base-100/30 backdrop-blur rounded-xl p-4 border border-base-content/5">
                            <!-- Main Office Address -->
                            <div class="flex items-start space-x-3 mb-3">
                                <div class="w-5 h-5 bg-primary/10 rounded-lg flex items-center justify-center flex-shrink-0 mt-0.5">
                                    <x-icon name="phosphor.map-pin" class="w-3 h-3 text-primary" />
                                </div>
                                <div class="text-sm">
                                    <p class="text-base-content font-medium mb-1">Kantor Operasional</p>
                                    <p class="text-base-content/70 leading-tight">
                                        {{ config('landingpage.contact.address.operational.street') }}<br>
                                        {{ config('landingpage.contact.address.operational.city') }}, {{ config('landingpage.contact.address.operational.province') }}
                                    </p>
                                </div>
                            </div>

                            <!-- Contact Methods -->
                            <div class="grid grid-cols-1 gap-2">
                                <!-- Phone -->
                                <a href="tel:{{ config('landingpage.contact.phone.primary') }}"
                                   class="flex items-center space-x-2 text-sm hover:text-primary transition-colors duration-200">
                                    <x-icon name="phosphor.phone" class="w-3 h-3 text-primary" />
                                    <span class="font-medium">{{ config('landingpage.contact.phone.primary') }}</span>
                                </a>

                                <!-- WhatsApp -->
                                @if(config('landingpage.contact.phone.whatsapp'))
                                <a href="https://wa.me/{{ str_replace(['+', ' ', '-'], '', config('landingpage.contact.phone.whatsapp')) }}"
                                   target="_blank"
                                   class="flex items-center space-x-2 text-sm hover:text-success transition-colors duration-200">
                                    <x-icon name="si.whatsapp" class="w-3 h-3 text-success" />
                                    <span class="font-medium">{{ config('landingpage.contact.phone.whatsapp') }}</span>
                                </a>
                                @endif

                                <!-- Email -->
                                <a href="mailto:{{ config('landingpage.contact.email.info') }}"
                                   class="flex items-center space-x-2 text-sm hover:text-info transition-colors duration-200">
                                    <x-icon name="phosphor.envelope" class="w-3 h-3 text-info" />
                                    <span class="font-medium">{{ config('landingpage.contact.email.info') }}</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistics Bar -->
        @if(config('landingpage.features_flags.show_statistics'))
        <div class="scroll-animate scale-in mb-12">
            <div class="relative group">
                <div class="absolute -inset-1 bg-gradient-to-r from-primary to-secondary rounded-2xl blur opacity-20 group-hover:opacity-30 transition-opacity duration-300"></div>

                <x-card class="relative bg-base-100/80 backdrop-blur border border-base-content/10 rounded-2xl p-6">
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-6 text-center">
                        <div class="group">
                            <div class="w-12 h-12 bg-primary/10 rounded-xl flex items-center justify-center mx-auto mb-2 group-hover:scale-110 transition-transform">
                                <x-icon name="phosphor.users-four" class="w-6 h-6 text-primary" />
                            </div>
                            <div class="text-2xl font-bold text-primary mb-1">{{ config('landingpage.stats.clients_served') }}</div>
                            <div class="text-sm text-base-content/60">Klien Terlayani</div>
                        </div>

                        <div class="group">
                            <div class="w-12 h-12 bg-secondary/10 rounded-xl flex items-center justify-center mx-auto mb-2 group-hover:scale-110 transition-transform">
                                <x-icon name="phosphor.files" class="w-6 h-6 text-secondary" />
                            </div>
                            <div class="text-2xl font-bold text-secondary mb-1">{{ config('landingpage.stats.documents_processed') }}</div>
                            <div class="text-sm text-base-content/60">Dokumen Diproses</div>
                        </div>

                        <div class="group">
                            <div class="w-12 h-12 bg-accent/10 rounded-xl flex items-center justify-center mx-auto mb-2 group-hover:scale-110 transition-transform">
                                <x-icon name="phosphor.map-pin-area" class="w-6 h-6 text-accent" />
                            </div>
                            <div class="text-2xl font-bold text-accent mb-1">{{ config('landingpage.stats.sultra_coverage') }}</div>
                            <div class="text-sm text-base-content/60">Daerah Sultra</div>
                        </div>

                        <div class="group">
                            <div class="w-12 h-12 bg-info/10 rounded-xl flex items-center justify-center mx-auto mb-2 group-hover:scale-110 transition-transform">
                                <x-icon name="phosphor.calendar-star" class="w-6 h-6 text-info" />
                            </div>
                            <div class="text-2xl font-bold text-info mb-1">{{ config('landingpage.stats.years_experience') }}</div>
                            <div class="text-sm text-base-content/60">Tahun Pengalaman</div>
                        </div>
                    </div>
                </x-card>
            </div>
        </div>
        @endif
    </div>

    <!-- Developer Credits & Copyright -->
    <div class="relative z-10 border-t border-base-content/10 bg-base-100/30 backdrop-blur">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="scroll-animate fade-in-up">
                <div class="flex flex-col lg:flex-row justify-between items-center space-y-6 lg:space-y-0">
                    <!-- Copyright & Legal -->
                    <div class="text-center lg:text-left">
                        <p class="text-base-content/70 mb-2">
                            &copy; {{ date('Y') }} {{ config('landingpage.legal.registered_name') }}.
                            Semua hak cipta dilindungi.
                        </p>
                        <p class="text-sm text-base-content/50 mb-2">
                            {{ config('landingpage.company_tagline') }} • {{ config('landingpage.business.primary_location') }}
                        </p>
                        @if(config('landingpage.features_flags.show_company_registration'))
                        <p class="text-xs text-base-content/40">
                            No. NPWP: {{ config('landingpage.legal.business_number') }} |
                            {{ config('landingpage.legal.entity_type') }}
                        </p>
                        @endif
                    </div>

                    <!-- Developer Credits -->
                    @if(config('landingpage.features_flags.show_developer_credits'))
                    <div class="text-center lg:text-left">
                        <p class="text-sm text-base-content/60 mb-3">Dikembangkan dengan ❤️ oleh:</p>

                        <div class="group relative">
                            <div class="absolute -inset-1 bg-gradient-to-r from-accent to-primary rounded-2xl blur opacity-20 group-hover:opacity-40 transition-opacity duration-300"></div>

                            <x-card class="relative bg-gradient-to-r from-accent/90 to-primary/90 backdrop-blur text-primary-content rounded-2xl p-4">
                                <div class="flex flex-col sm:flex-row items-center space-y-3 sm:space-y-0 sm:space-x-4">
                                    <div class="relative">
                                        <x-avatar
                                            image="https://avatars.githubusercontent.com/u/56766058?v=4"
                                            :alt="config('landingpage.developer.name')"
                                            class="relative h-18 w-18 ring-2 ring-primary-content/30">
                                            <x-slot:title class="mb-1">
                                                <div class="flex flex-col items-start">
                                                    <h4 class="font-extrabold text-md text-base-100">
                                                        {{ config('landingpage.developer.name') }}
                                                    </h4>
                                                    <p class="font-light text-sm text-base-300">
                                                        {{ config('landingpage.developer.role') }}
                                                    </p>
                                                </div>
                                            </x-slot:title>
                                            <x-slot:subtitle>
                                                <div class="grid grid-cols-2 gap-2 items-start">
                                                    <x-button
                                                        link="{{ config('landingpage.developer.website') }}"
                                                        external icon="phosphor.globe"
                                                        class="btn-base-content btn-sm"
                                                        label="{{ config('landingpage.developer.company') }}" />
                                                    <x-button
                                                        link="{{ config('landingpage.developer.github') }}"
                                                        external icon="si.github"
                                                        class="btn-base-content btn-sm"
                                                        label="GitHub" />
                                                </div>
                                            </x-slot:subtitle>
                                        </x-avatar>
                                    </div>
                                </div>
                            </x-card>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Floating Elements -->
    <div class="absolute top-32 right-10 w-4 h-4 bg-primary/20 rounded-full animate-pulse hidden lg:block"></div>
    <div class="absolute top-96 left-20 w-6 h-6 bg-secondary/20 rounded-full animate-pulse delay-1000 hidden lg:block"></div>
    <div class="absolute bottom-40 right-20 w-3 h-3 bg-accent/20 rounded-full animate-pulse delay-2000 hidden lg:block"></div>
</footer>
