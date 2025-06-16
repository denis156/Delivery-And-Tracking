<?php

namespace App\Livewire\LandingPage\Sections;

use Livewire\Component;

class BenefitsSection extends Component
{
    public function render()
    {
        return <<<'HTML'
        <section id="benefits" class="relative py-20 bg-gradient-to-br from-base-100 via-base-200 to-base-100 overflow-hidden">

            <!-- Background Effects -->
            <div class="absolute inset-0 bg-grid-pattern opacity-5"></div>
            <div class="absolute top-0 left-0 w-96 h-96 bg-accent/10 rounded-full blur-3xl -translate-x-1/2 -translate-y-1/2"></div>
            <div class="absolute bottom-0 right-0 w-96 h-96 bg-info/10 rounded-full blur-3xl translate-x-1/2 translate-y-1/2"></div>

            <div class="relative z-10 max-w-7xl mx-auto px-4">
                <!-- Section Header -->
                <div class="text-center mb-16">
                    <div class="scroll-animate fade-in-down">
                        <div class="gap-2 badge badge-xl badge-outline badge-success bg-success/20 mb-6">
                            <div aria-label="info" class="status status-success status-md animate-pulse"></div>
                            <span class="text-sm font-medium text-success">Manfaat Utama</span>
                        </div>
                    </div>

                    <div class="scroll-animate">
                        <h2 class="text-4xl md:text-6xl font-bold mb-6 leading-tight">
                            <span class="block text-base-content">Keunggulan</span>
                            <span class="block bg-gradient-to-r from-primary via-secondary to-success bg-clip-text text-transparent">
                                {{ config('app.name') }}
                            </span>
                        </h2>
                        <p class="text-lg md:text-xl text-base-content/70 max-w-3xl mx-auto">
                            Solusi digital yang memberikan dampak nyata untuk {{ config('landingpage.company_name') }}
                            dalam mengoptimalkan operasi {{ config('landingpage.company_description', 'transportasi') }}
                        </p>
                    </div>
                </div>

                <!-- Main Benefits Grid -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 mb-20">
                    <!-- Left Side - Benefits List -->
                    <div class="space-y-6">

                        <!-- Benefit 1: Efisiensi Operasional -->
                        <div class="scroll-animate fade-in-left">
                            <div class="group relative">
                                <div class="absolute -inset-0.5 bg-gradient-to-r from-primary to-secondary rounded-2xl blur opacity-20 group-hover:opacity-40 transition-opacity duration-300"></div>

                                <x-card class="relative bg-base-100/90 backdrop-blur border border-base-content/10 rounded-2xl p-6 hover:bg-base-100 transition-all duration-300">
                                    <div class="flex items-start space-x-4">
                                        <div class="w-14 h-14 bg-gradient-to-br from-primary/20 to-primary/10 flex-shrink-0 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform">
                                            <x-icon name="phosphor.lightning" class="w-7 h-7 text-primary" />
                                        </div>
                                        <div class="flex-1">
                                            <h3 class="text-xl font-bold text-base-content mb-3">Efisiensi Operasional</h3>
                                            <p class="text-base-content/70 mb-4 leading-relaxed">
                                                Meningkatkan efisiensi operasional hingga <strong class="text-primary">{{ config('landingpage.stats.efficiency_improvement') }}</strong>
                                                dengan digitalisasi workflow dan eliminasi proses manual berulang untuk {{ config('landingpage.business.primary_location') }}.
                                            </p>
                                            <div class="flex flex-wrap gap-2">
                                                <span class="px-3 py-1 badge bg-primary/20 text-primary rounded-full text-sm font-medium">Digital Workflow</span>
                                                <span class="px-3 py-1 badge bg-secondary/20 text-secondary rounded-full text-sm font-medium">Otomatisasi</span>
                                                <span class="px-3 py-1 badge bg-accent/20 text-accent rounded-full text-sm font-medium">{{ config('landingpage.stats.efficiency_improvement') }} Faster</span>
                                            </div>
                                        </div>
                                    </div>
                                </x-card>
                            </div>
                        </div>

                        <!-- Benefit 2: Transparansi Real-Time -->
                        <div class="scroll-animate fade-in-left">
                            <div class="group relative">
                                <div class="absolute -inset-0.5 bg-gradient-to-r from-secondary to-accent rounded-2xl blur opacity-20 group-hover:opacity-40 transition-opacity duration-300"></div>

                                <x-card class="relative bg-base-100/90 backdrop-blur border border-base-content/10 rounded-2xl p-6 hover:bg-base-100 transition-all duration-300">
                                    <div class="flex items-start space-x-4">
                                        <div class="w-14 h-14 bg-gradient-to-br from-secondary/20 to-secondary/10 flex-shrink-0 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform">
                                            <x-icon name="phosphor.eye" class="w-7 h-7 text-secondary" />
                                        </div>
                                        <div class="flex-1">
                                            <h3 class="text-xl font-bold text-base-content mb-3">Transparansi Real-Time</h3>
                                            <p class="text-base-content/70 mb-4 leading-relaxed">
                                                Monitoring ketersediaan sistem {{ config('landingpage.stats.uptime') }} dengan visibility penuh untuk semua stakeholder.
                                                Update status otomatis di setiap tahap pengiriman untuk wilayah {{ config('landingpage.business.primary_location') }}.
                                            </p>
                                            <div class="flex flex-wrap gap-2">
                                                <span class="px-3 py-1 badge bg-secondary/20 text-secondary rounded-full text-sm font-medium">Real-time</span>
                                                <span class="px-3 py-1 badge bg-accent/20 text-accent rounded-full text-sm font-medium">{{ config('landingpage.stats.uptime') }}</span>
                                                <span class="px-3 py-1 badge bg-info/20 text-info rounded-full text-sm font-medium">Transparency</span>
                                            </div>
                                        </div>
                                    </div>
                                </x-card>
                            </div>
                        </div>

                        <!-- Benefit 3: Akurasi Data -->
                        <div class="scroll-animate fade-in-left">
                            <div class="group relative">
                                <div class="absolute -inset-0.5 bg-gradient-to-r from-accent to-info rounded-2xl blur opacity-20 group-hover:opacity-40 transition-opacity duration-300"></div>

                                <x-card class="relative bg-base-100/90 backdrop-blur border border-base-content/10 rounded-2xl p-6 hover:bg-base-100 transition-all duration-300">
                                    <div class="flex items-start space-x-4">
                                        <div class="w-14 h-14 bg-gradient-to-br from-accent/20 to-accent/10 flex-shrink-0 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform">
                                            <x-icon name="phosphor.target" class="w-7 h-7 text-accent" />
                                        </div>
                                        <div class="flex-1">
                                            <h3 class="text-xl font-bold text-base-content mb-3">Akurasi Data Tinggi</h3>
                                            <p class="text-base-content/70 mb-4 leading-relaxed">
                                                Mencapai akurasi data hingga <strong class="text-accent">{{ config('landingpage.stats.data_accuracy') }}</strong>
                                                dengan validasi otomatis dan eliminasi human error dalam pengelolaan {{ config('landingpage.stats.documents_processed') }} dokumen.
                                            </p>
                                            <div class="flex flex-wrap gap-2">
                                                <span class="px-3 py-1 badge bg-accent/20 text-accent rounded-full text-sm font-medium">{{ config('landingpage.stats.data_accuracy') }} Akurat</span>
                                                <span class="px-3 py-1 badge bg-info/20 text-info rounded-full text-sm font-medium">Auto Validasi</span>
                                                <span class="px-3 py-1 badge bg-success/20 text-success rounded-full text-sm font-medium">Zero Error</span>
                                            </div>
                                        </div>
                                    </div>
                                </x-card>
                            </div>
                        </div>

                        <!-- Benefit 4: Digital Transformation -->
                        <div class="scroll-animate fade-in-left">
                            <div class="group relative">
                                <div class="absolute -inset-0.5 bg-gradient-to-r from-success to-primary rounded-2xl blur opacity-20 group-hover:opacity-40 transition-opacity duration-300"></div>

                                <x-card class="relative bg-base-100/90 backdrop-blur border border-base-content/10 rounded-2xl p-6 hover:bg-base-100 transition-all duration-300">
                                    <div class="flex items-start space-x-4">
                                        <div class="w-14 h-14 bg-gradient-to-br from-success/20 to-success/10 flex-shrink-0 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform">
                                            <x-icon name="phosphor.leaf" class="w-7 h-7 text-success" />
                                        </div>
                                        <div class="flex-1">
                                            <h3 class="text-xl font-bold text-base-content mb-3">Transformasi Digital</h3>
                                            <p class="text-base-content/70 mb-4 leading-relaxed">
                                                Digitalisasi {{ config('landingpage.stats.paperless_percentage') }} proses dokumentasi dengan pengalaman
                                                {{ config('landingpage.stats.years_experience') }} tahun dalam industri transportasi {{ config('landingpage.business.primary_location') }}.
                                            </p>
                                            <div class="flex flex-wrap gap-2">
                                                <span class="px-3 py-1 badge bg-success/20 text-success rounded-full text-sm font-medium">{{ config('landingpage.stats.paperless_percentage') }} Digital</span>
                                                <span class="px-3 py-1 badge bg-accent/20 text-accent rounded-full text-sm font-medium">{{ config('landingpage.stats.years_experience') }} Tahun</span>
                                                <span class="px-3 py-1 badge bg-info/20 text-info rounded-full text-sm font-medium">Cloud Ready</span>
                                            </div>
                                        </div>
                                    </div>
                                </x-card>
                            </div>
                        </div>
                    </div>

                    <!-- Right Side - Visual Stats -->
                    <div class="flex items-center justify-center">
                        <div class="scroll-animate scale-in">
                            <div class="relative group">
                                <!-- Glow Effect -->
                                <div class="absolute -inset-1 bg-gradient-to-r from-primary to-secondary rounded-3xl blur-lg opacity-30 group-hover:opacity-50 transition-opacity duration-300"></div>

                                <!-- Main Stats Card -->
                                <x-card class="relative bg-base-100/90 backdrop-blur border border-base-content/10 rounded-3xl p-8 w-80 h-80">
                                    <div class="text-center mb-6">
                                        <div class="w-16 h-16 bg-gradient-to-br from-primary/20 to-secondary/20 rounded-2xl flex items-center justify-center mx-auto mb-4">
                                            <x-icon name="phosphor.presentation-chart" class="w-8 h-8 text-primary" />
                                        </div>
                                        <h4 class="text-xl font-bold text-base-content">Monitoring Center</h4>
                                        <p class="text-base-content/60 text-sm">Pusat kendali {{ config('landingpage.company_short') }}</p>
                                    </div>

                                    <!-- Stats Grid -->
                                    <div class="grid grid-cols-2 gap-4">
                                        <x-card class="bg-base-100/80 backdrop-blur border border-base-content/10 rounded-xl p-3 text-center hover:bg-base-100 transition-colors">
                                            <div class="text-2xl font-bold text-primary mb-1">{{ config('landingpage.stats.efficiency_improvement') }}</div>
                                            <div class="text-xs text-base-content/60">Efisiensi</div>
                                        </x-card>
                                        <x-card class="bg-base-100/80 backdrop-blur border border-base-content/10 rounded-xl p-3 text-center hover:bg-base-100 transition-colors">
                                            <div class="text-2xl font-bold text-secondary mb-1">{{ config('landingpage.stats.data_accuracy') }}</div>
                                            <div class="text-xs text-base-content/60">Akurasi</div>
                                        </x-card>
                                        <x-card class="bg-base-100/80 backdrop-blur border border-base-content/10 rounded-xl p-3 text-center hover:bg-base-100 transition-colors">
                                            <div class="text-2xl font-bold text-accent mb-1">{{ config('landingpage.stats.clients_served') }}</div>
                                            <div class="text-xs text-base-content/60">Klien</div>
                                        </x-card>
                                        <x-card class="bg-base-100/80 backdrop-blur border border-base-content/10 rounded-xl p-3 text-center hover:bg-base-100 transition-colors">
                                            <div class="text-2xl font-bold text-info mb-1">{{ config('landingpage.stats.paperless_percentage') }}</div>
                                            <div class="text-xs text-base-content/60">Digital</div>
                                        </x-card>
                                    </div>
                                </x-card>

                                <!-- Floating Elements -->
                                <div class="absolute -top-4 -right-4 w-16 h-16 bg-gradient-to-br from-primary to-primary/80 rounded-full flex items-center justify-center shadow-lg animate-bounce">
                                    <x-icon name="phosphor.shipping-container" class="w-8 h-8 text-primary-content" />
                                </div>
                                <div class="absolute -bottom-4 -left-4 w-16 h-16 bg-gradient-to-br from-secondary to-secondary/80 rounded-full flex items-center justify-center shadow-lg animate-bounce delay-1000">
                                    <x-icon name="phosphor.map-pin" class="w-8 h-8 text-secondary-content" />
                                </div>
                                <div class="absolute top-1/2 -left-8 w-12 h-12 bg-gradient-to-br from-accent to-accent/80 rounded-full flex items-center justify-center shadow-lg animate-bounce delay-2000">
                                    <x-icon name="phosphor.file-text" class="w-6 h-6 text-accent-content" />
                                </div>
                                <div class="absolute top-1/2 -right-8 w-12 h-12 bg-gradient-to-br from-info to-info/80 rounded-full flex items-center justify-center shadow-lg animate-bounce delay-500">
                                    <x-icon name="phosphor.chart-bar" class="w-6 h-6 text-info-content" />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Call to Action -->
                <div class="scroll-animate scale-in">
                    <div class="relative group">
                        <div class="absolute -inset-1 bg-gradient-to-r from-primary to-secondary rounded-3xl blur-lg opacity-30 group-hover:opacity-50 transition-opacity duration-300"></div>

                        <x-card class="relative bg-gradient-to-r from-primary to-secondary text-primary-content rounded-3xl p-8">
                            <div class="text-center">
                                <h3 class="text-3xl md:text-4xl font-bold mb-4">
                                    Siap Transformasi Digital?
                                </h3>
                                <p class="text-lg mb-8 opacity-90 max-w-2xl mx-auto">
                                    Bergabunglah dengan {{ config('landingpage.stats.clients_served') }} klien yang telah mempercayai
                                    {{ config('landingpage.company_name') }} untuk mengoptimalkan operasi transportasi di {{ config('landingpage.business.primary_location') }}
                                </p>
                                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                                    <x-button
                                        label="Mulai Konsultasi"
                                        icon="phosphor.rocket-launch"
                                        link="#contact"
                                        class="btn-accent btn-lg shadow-xl hover:shadow-2xl hover:scale-105 transition-all duration-300" />
                                    <x-button
                                        label="Hubungi Kami"
                                        icon="phosphor.phone"
                                        link="tel:{{ config('landingpage.contact.phone.primary') }}"
                                        class="btn-ghost btn-lg border-2 border-primary-content text-primary-content hover:bg-primary-content hover:text-primary transition-all duration-300" />
                                </div>
                            </div>
                        </x-card>
                    </div>
                </div>
            </div>

            <!-- Floating Elements -->
            <div class="absolute top-20 right-10 w-4 h-4 bg-accent/30 rounded-full animate-pulse hidden lg:block"></div>
            <div class="absolute top-60 left-20 w-6 h-6 bg-info/30 rounded-full animate-pulse delay-1000 hidden lg:block"></div>
            <div class="absolute bottom-40 right-20 w-3 h-3 bg-primary/30 rounded-full animate-pulse delay-2000 hidden lg:block"></div>
        </section>
        HTML;
    }
}
