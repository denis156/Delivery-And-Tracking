<?php

namespace App\Livewire\LandingPage\Sections;

use Livewire\Component;

class FeaturesSection extends Component
{
    public function render()
    {
        return <<<'HTML'
        <section id="features" class="relative py-20 bg-gradient-to-br from-base-200 via-base-100 to-base-200 overflow-hidden">

            <!-- Background Effects -->
            <div class="absolute inset-0 bg-grid-pattern opacity-5"></div>
            <div class="absolute top-0 right-0 w-80 h-80 bg-secondary/10 rounded-full blur-3xl translate-x-1/2 -translate-y-1/2"></div>
            <div class="absolute bottom-0 left-0 w-80 h-80 bg-primary/10 rounded-full blur-3xl -translate-x-1/2 translate-y-1/2"></div>

            <div class="relative z-10 max-w-7xl mx-auto px-4">
                <!-- Section Header -->
                <div class="text-center mb-16">
                    <div class="scroll-animate fade-in-down">
                        <div class="gap-2 badge badge-xl badge-outline badge-accent bg-accent/20 mb-6">
                            <div aria-label="info" class="status status-accent status-md animate-pulse"></div>
                            <span class="text-sm font-medium text-accent">Fitur Terlengkap</span>
                        </div>
                    </div>

                    <div class="scroll-animate">
                        <h2 class="text-4xl md:text-6xl font-bold mb-6 leading-tight">
                            <span class="block text-base-content">Fitur Unggulan</span>
                            <span class="block bg-gradient-to-r from-primary via-secondary to-accent bg-clip-text text-transparent">
                                {{ config('app.name') }}
                            </span>
                        </h2>
                        <p class="text-lg md:text-xl text-base-content/70 max-w-3xl mx-auto">
                            Sistem terintegrasi dengan {{ config('landingpage.features.multi_role_count') }} role berbeda
                            untuk mengoptimalkan operasi {{ config('landingpage.company_description') }} di {{ config('landingpage.business.primary_location') }}
                        </p>
                    </div>
                </div>

                <!-- Features Grid -->
                <div class="scroll-animate-container">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 mb-16">

                        <!-- Feature 1: Real-time Tracking -->
                        <div class="scroll-animate fade-in-left h-full">
                            <div class="group relative h-full">
                                <!-- Glow Effect -->
                                <div class="absolute -inset-0.5 bg-gradient-to-r from-primary to-secondary rounded-2xl blur opacity-20 group-hover:opacity-40 transition-opacity duration-300"></div>

                                <!-- Card Content -->
                                <x-card class="relative bg-base-100/90 backdrop-blur border border-base-content/10 rounded-2xl p-6 hover:bg-base-100 transition-all duration-300 hover:-translate-y-2 h-full flex flex-col">
                                    <div class="w-16 h-16 bg-gradient-to-br from-primary/20 to-primary/10 rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform mx-auto">
                                        <x-icon name="phosphor.map-trifold" class="w-8 h-8 text-primary" />
                                    </div>

                                    <h3 class="text-xl font-bold mb-3 text-center">GPS Tracking Real-Time</h3>
                                    <p class="text-base-content/70 mb-6 leading-relaxed text-center flex-grow">
                                        Pantau lokasi armada di seluruh {{ config('landingpage.business.primary_location') }} dengan akurasi tinggi.
                                        Berbasis teknologi WebSocket untuk update real-time.
                                    </p>

                                    <div class="flex flex-wrap gap-2 justify-center mt-auto">
                                        <span class="px-3 py-1 bg-primary/20 text-primary rounded-full text-sm font-medium">WebSocket</span>
                                        <span class="px-3 py-1 bg-secondary/20 text-secondary rounded-full text-sm font-medium">Real-time</span>
                                    </div>
                                </x-card>
                            </div>
                        </div>

                        <!-- Feature 2: Multi-role System -->
                        <div class="scroll-animate scale-in h-full">
                            <div class="group relative h-full">
                                <div class="absolute -inset-0.5 bg-gradient-to-r from-secondary to-accent rounded-2xl blur opacity-20 group-hover:opacity-40 transition-opacity duration-300"></div>

                                <x-card class="relative bg-base-100/90 backdrop-blur border border-base-content/10 rounded-2xl p-6 hover:bg-base-100 transition-all duration-300 hover:-translate-y-2 h-full flex flex-col">
                                    <div class="w-16 h-16 bg-gradient-to-br from-secondary/20 to-secondary/10 rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform mx-auto">
                                        <x-icon name="phosphor.users" class="w-8 h-8 text-secondary" />
                                    </div>

                                    <h3 class="text-xl font-bold mb-3 text-center">Multi-Role System</h3>
                                    <p class="text-base-content/70 mb-6 leading-relaxed text-center flex-grow">
                                        Sistem dengan {{ config('landingpage.features.multi_role_count') }} role berbeda: Manager, Admin, Pengemudi, Client,
                                        Petugas Lapangan, Petugas Ruangan, dan Petugas Gudang untuk {{ config('landingpage.company_name') }}.
                                    </p>

                                    <div class="flex flex-wrap gap-2 justify-center mt-auto">
                                        <span class="px-3 py-1 bg-accent/20 text-accent rounded-full text-sm font-medium">{{ config('landingpage.features.multi_role_count') }} Roles</span>
                                        <span class="px-3 py-1 bg-info/20 text-info rounded-full text-sm font-medium">Secure Access</span>
                                    </div>
                                </x-card>
                            </div>
                        </div>

                        <!-- Feature 3: Digital Workflow -->
                        <div class="scroll-animate fade-in-right h-full">
                            <div class="group relative h-full">
                                <div class="absolute -inset-0.5 bg-gradient-to-r from-accent to-info rounded-2xl blur opacity-20 group-hover:opacity-40 transition-opacity duration-300"></div>

                                <x-card class="relative bg-base-100/90 backdrop-blur border border-base-content/10 rounded-2xl p-6 hover:bg-base-100 transition-all duration-300 hover:-translate-y-2 h-full flex flex-col">
                                    <div class="w-16 h-16 bg-gradient-to-br from-accent/20 to-accent/10 rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform mx-auto">
                                        <x-icon name="phosphor.gear-six" class="w-8 h-8 text-accent" />
                                    </div>

                                    <h3 class="text-xl font-bold mb-3 text-center">Digital Workflow</h3>
                                    <p class="text-base-content/70 mb-6 leading-relaxed text-center flex-grow">
                                        Digitalisasi {{ config('landingpage.stats.paperless_percentage') }} proses dengan workflow otomatis:
                                        Input → Validasi → Assign → Tracking → Selesai.
                                    </p>

                                    <div class="flex flex-wrap gap-2 justify-center mt-auto">
                                        <span class="px-3 py-1 bg-success/20 text-success rounded-full text-sm font-medium">{{ config('landingpage.stats.paperless_percentage') }} Digital</span>
                                        <span class="px-3 py-1 bg-warning/20 text-warning rounded-full text-sm font-medium">Auto Flow</span>
                                    </div>
                                </x-card>
                            </div>
                        </div>

                        <!-- Feature 4: Digital Surat Jalan -->
                        <div class="scroll-animate fade-in-left h-full">
                            <div class="group relative h-full">
                                <div class="absolute -inset-0.5 bg-gradient-to-r from-info to-primary rounded-2xl blur opacity-20 group-hover:opacity-40 transition-opacity duration-300"></div>

                                <x-card class="relative bg-base-100/90 backdrop-blur border border-base-content/10 rounded-2xl p-6 hover:bg-base-100 transition-all duration-300 hover:-translate-y-2 h-full flex flex-col">
                                    <div class="w-16 h-16 bg-gradient-to-br from-info/20 to-info/10 rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform mx-auto">
                                        <x-icon name="phosphor.files" class="w-8 h-8 text-info" />
                                    </div>

                                    <h3 class="text-xl font-bold mb-3 text-center">Surat Jalan Digital</h3>
                                    <p class="text-base-content/70 mb-6 leading-relaxed text-center flex-grow">
                                        Telah memproses {{ config('landingpage.stats.documents_processed') }} dokumen digital dengan template yang dapat disesuaikan.
                                        Cetak fisik untuk pengemudi, simpan digital untuk arsip.
                                    </p>

                                    <div class="flex flex-wrap gap-2 justify-center mt-auto">
                                        <span class="px-3 py-1 bg-error/20 text-error rounded-full text-sm font-medium">{{ config('landingpage.stats.documents_processed') }}</span>
                                        <span class="px-3 py-1 bg-primary/20 text-primary rounded-full text-sm font-medium">Paperless</span>
                                    </div>
                                </x-card>
                            </div>
                        </div>

                        <!-- Feature 5: Analytics & Reporting -->
                        <div class="scroll-animate scale-in h-full">
                            <div class="group relative h-full">
                                <div class="absolute -inset-0.5 bg-gradient-to-r from-success to-secondary rounded-2xl blur opacity-20 group-hover:opacity-40 transition-opacity duration-300"></div>

                                <x-card class="relative bg-base-100/90 backdrop-blur border border-base-content/10 rounded-2xl p-6 hover:bg-base-100 transition-all duration-300 hover:-translate-y-2 h-full flex flex-col">
                                    <div class="w-16 h-16 bg-gradient-to-br from-success/20 to-success/10 rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform mx-auto">
                                        <x-icon name="phosphor.chart-bar" class="w-8 h-8 text-success" />
                                    </div>

                                    <h3 class="text-xl font-bold mb-3 text-center">Analytics & Reporting</h3>
                                    <p class="text-base-content/70 mb-6 leading-relaxed text-center flex-grow">
                                        Dashboard analytics dengan akurasi {{ config('landingpage.stats.data_accuracy') }} dan
                                        visualisasi data untuk {{ config('landingpage.stats.clients_served') }} klien aktif.
                                    </p>

                                    <div class="flex flex-wrap gap-2 justify-center mt-auto">
                                        <span class="px-3 py-1 bg-warning/20 text-warning rounded-full text-sm font-medium">{{ config('landingpage.stats.data_accuracy') }}</span>
                                        <span class="px-3 py-1 bg-success/20 text-success rounded-full text-sm font-medium">Insights</span>
                                    </div>
                                </x-card>
                            </div>
                        </div>

                        <!-- Feature 6: Mobile Support -->
                        <div class="scroll-animate fade-in-right h-full">
                            <div class="group relative h-full">
                                <div class="absolute -inset-0.5 bg-gradient-to-r from-error to-warning rounded-2xl blur opacity-20 group-hover:opacity-40 transition-opacity duration-300"></div>

                                <x-card class="relative bg-base-100/90 backdrop-blur border border-base-content/10 rounded-2xl p-6 hover:bg-base-100 transition-all duration-300 hover:-translate-y-2 h-full flex flex-col">
                                    <div class="w-16 h-16 bg-gradient-to-br from-error/20 to-error/10 rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform mx-auto">
                                        <x-icon name="phosphor.device-mobile-camera" class="w-8 h-8 text-error" />
                                    </div>

                                    <h3 class="text-xl font-bold mb-3 text-center">Mobile Ready</h3>
                                    <p class="text-base-content/70 mb-6 leading-relaxed text-center flex-grow">
                                        Interface responsif dan support React Native WebView untuk pengalaman mobile optimal
                                        bagi pengemudi di {{ config('landingpage.business.primary_location') }}.
                                    </p>

                                    <div class="flex flex-wrap gap-2 justify-center mt-auto">
                                        <span class="px-3 py-1 bg-info/20 text-info rounded-full text-sm font-medium">Responsive</span>
                                        <span class="px-3 py-1 bg-error/20 text-error rounded-full text-sm font-medium">Mobile First</span>
                                    </div>
                                </x-card>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Technology Stack -->
                <div class="scroll-animate scale-in">
                    <div class="relative group">
                        <div class="absolute -inset-1 bg-gradient-to-r from-primary to-secondary rounded-3xl blur-lg opacity-20 group-hover:opacity-30 transition-opacity duration-300"></div>

                        <x-card class="relative bg-base-100/90 backdrop-blur border border-base-content/10 rounded-3xl p-8">
                            <div class="text-center mb-8">
                                <h3 class="text-3xl font-bold mb-4">Teknologi Terdepan</h3>
                                <p class="text-base-content/70 max-w-2xl mx-auto">
                                    Dibangun oleh {{ config('landingpage.developer.company') }} dengan stack teknologi modern
                                    dan terpercaya untuk performa maksimal {{ config('landingpage.company_name') }}
                                </p>
                            </div>

                            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-6">
                                <!-- Laravel -->
                                <div class="scroll-animate fade-in-up group">
                                    <div class="flex flex-col items-center p-4 bg-base-100/80 backdrop-blur border border-base-content/10 rounded-xl hover:bg-base-100 hover:border-red-300 hover:shadow-lg transition-all duration-300 hover:-translate-y-1">
                                        <div class="w-12 h-12 rounded-full flex items-center justify-center bg-red-100 mb-3 group-hover:bg-red-200 transition-colors">
                                            <x-icon name="si.laravel" class="w-6 h-6 text-red-600" />
                                        </div>
                                        <h4 class="font-semibold text-sm">Laravel 12</h4>
                                        <p class="text-xs text-base-content/60 text-center">Backend Framework</p>
                                    </div>
                                </div>

                                <!-- Livewire -->
                                <div class="scroll-animate fade-in-up group">
                                    <div class="flex flex-col items-center p-4 bg-base-100/80 backdrop-blur border border-base-content/10 rounded-xl hover:bg-base-100 hover:border-blue-300 hover:shadow-lg transition-all duration-300 hover:-translate-y-1">
                                        <div class="w-12 h-12 rounded-full flex items-center justify-center bg-blue-100 mb-3 group-hover:bg-blue-200 transition-colors">
                                            <x-icon name="si.livewire" class="w-6 h-6 text-blue-600" />
                                        </div>
                                        <h4 class="font-semibold text-sm">Livewire 3</h4>
                                        <p class="text-xs text-base-content/60 text-center">Reactive UI</p>
                                    </div>
                                </div>

                                <!-- DaisyUI -->
                                <div class="scroll-animate fade-in-up group">
                                    <div class="flex flex-col items-center p-4 bg-base-100/80 backdrop-blur border border-base-content/10 rounded-xl hover:bg-base-100 hover:border-green-300 hover:shadow-lg transition-all duration-300 hover:-translate-y-1">
                                        <div class="w-12 h-12 rounded-full flex items-center justify-center bg-green-100 mb-3 group-hover:bg-green-200 transition-colors">
                                            <x-icon name="si.daisyui" class="w-6 h-6 text-green-600" />
                                        </div>
                                        <h4 class="font-semibold text-sm">DaisyUI 5</h4>
                                        <p class="text-xs text-base-content/60 text-center">UI Components</p>
                                    </div>
                                </div>

                                <!-- Mary UI -->
                                <div class="scroll-animate fade-in-up group">
                                    <div class="flex flex-col items-center p-4 bg-base-100/80 backdrop-blur border border-base-content/10 rounded-xl hover:bg-base-100 hover:border-purple-300 hover:shadow-lg transition-all duration-300 hover:-translate-y-1">
                                        <div class="w-12 h-12 rounded-full flex items-center justify-center bg-purple-100 mb-3 group-hover:bg-purple-200 transition-colors">
                                            <x-icon name="phosphor.squares-four" class="w-6 h-6 text-purple-600" />
                                        </div>
                                        <h4 class="font-semibold text-sm">Mary UI</h4>
                                        <p class="text-xs text-base-content/60 text-center">Components</p>
                                    </div>
                                </div>

                                <!-- Leaflet -->
                                <div class="scroll-animate fade-in-up group">
                                    <div class="flex flex-col items-center p-4 bg-base-100/80 backdrop-blur border border-base-content/10 rounded-xl hover:bg-base-100 hover:border-amber-300 hover:shadow-lg transition-all duration-300 hover:-translate-y-1">
                                        <div class="w-12 h-12 rounded-full flex items-center justify-center bg-amber-100 mb-3 group-hover:bg-amber-200 transition-colors">
                                            <x-icon name="si.leaflet" class="w-6 h-6 text-amber-600" />
                                        </div>
                                        <h4 class="font-semibold text-sm">Leaflet.js</h4>
                                        <p class="text-xs text-base-content/60 text-center">Maps & GPS</p>
                                    </div>
                                </div>

                                <!-- React Native -->
                                <div class="scroll-animate fade-in-up group">
                                    <div class="flex flex-col items-center p-4 bg-base-100/80 backdrop-blur border border-base-content/10 rounded-xl hover:bg-base-100 hover:border-cyan-300 hover:shadow-lg transition-all duration-300 hover:-translate-y-1">
                                        <div class="w-12 h-12 rounded-full flex items-center justify-center bg-cyan-100 mb-3 group-hover:bg-cyan-200 transition-colors">
                                            <x-icon name="si.react" class="w-6 h-6 text-cyan-600" />
                                        </div>
                                        <h4 class="font-semibold text-sm">React Native</h4>
                                        <p class="text-xs text-base-content/60 text-center">Mobile App</p>
                                    </div>
                                </div>
                            </div>
                        </x-card>
                    </div>
                </div>
            </div>

            <!-- Floating Elements -->
            <div class="absolute top-40 left-10 w-3 h-3 bg-primary/30 rounded-full animate-pulse hidden lg:block"></div>
            <div class="absolute top-60 right-20 w-4 h-4 bg-secondary/30 rounded-full animate-pulse delay-1000 hidden lg:block"></div>
            <div class="absolute bottom-40 left-20 w-2 h-2 bg-accent/30 rounded-full animate-pulse delay-2000 hidden lg:block"></div>
        </section>
        HTML;
    }
}
