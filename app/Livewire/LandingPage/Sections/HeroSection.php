<?php

namespace App\Livewire\LandingPage\Sections;

use Livewire\Component;

class HeroSection extends Component
{
    public function render()
    {
        return <<<'HTML'
        <section id="home" class="relative min-h-dvh overflow-hidden bg-gradient-to-br from-base-100 via-base-200 to-base-300">
            <!-- Background Effects -->
            <div class="absolute inset-0 bg-grid-pattern opacity-5"></div>
            <div class="absolute top-0 left-0 w-96 h-96 bg-primary/10 rounded-full blur-3xl -translate-x-1/2 -translate-y-1/2"></div>
            <div class="absolute bottom-0 right-0 w-96 h-96 bg-secondary/10 rounded-full blur-3xl translate-x-1/2 translate-y-1/2"></div>
            <div class="absolute top-1/2 left-1/2 w-64 h-64 bg-accent/5 rounded-full blur-2xl transform -translate-x-1/2 -translate-y-1/2"></div>

            <div class="relative z-10 flex items-center justify-center min-h-dvh px-4 py-12">
                <div class="text-center max-w-6xl mx-auto">

                    <!-- Floating Badge - Perfectly Centered -->
                    <div class="scroll-animate fade-in-down">
                        <div class="flex justify-center mb-8">
                            <div class="gap-2 badge badge-xl badge-outline badge-primary bg-primary/20 mb-6">
                                <div aria-label="info" class="status status-primary status-md animate-pulse"></div>
                                <span class="text-sm font-medium text-primary">Sistem Tracking Real-Time</span>
                            </div>
                        </div>
                    </div>

                    <!-- Hero Title - Centered -->
                    <div class="scroll-animate">
                        <h1 class="text-5xl md:text-7xl lg:text-8xl font-bold mb-6 leading-tight text-center">
                            <span class="block text-base-content">Revolusi Digital</span>
                            <span class="block bg-gradient-to-r from-primary via-secondary to-accent bg-clip-text text-transparent">
                                {{ config('landingpage.business.primary_location') }}
                            </span>
                        </h1>
                    </div>

                    <!-- Hero Subtitle - Centered -->
                    <div class="scroll-animate">
                        <div class="flex justify-center mb-12">
                            <p class="text-xl md:text-2xl text-base-content/70 max-w-4xl leading-relaxed text-center">
                                Solusi transportasi cerdas dengan teknologi
                                <span class="font-semibold text-primary">WebSocket Real-Time</span> untuk
                                <span class="font-semibold text-accent">{{ config('landingpage.company_name') }}</span> -
                                {{ config('landingpage.company_description') }} terdepan di {{ config('landingpage.business.primary_location') }}
                            </p>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="scroll-animate">
                        <div class="flex flex-col sm:flex-row gap-4 justify-center mb-16">
                            <!-- Driver Button -->
                            <x-button link="{{ route('driver.dashboard') }}" class="btn-info btn-lg btn-outline" label="Portal Pengemudi"
                                icon="phosphor.truck-trailer" />

                            <!-- Management App Button -->
                            <x-button link="{{ route('app.dashboard') }}" wire:navigate.hover class="btn-primary btn-lg"
                                label="Akses Dashboard" icon="phosphor.gauge" />
                        </div>
                    </div>

                    <!-- Feature Cards Grid -->
                    <div class="scroll-animate-container">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-16">
                            <!-- Feature 1 -->
                            <div class="scroll-animate fade-in-left">
                                <x-card class="group p-6 bg-base-100/50 backdrop-blur border border-base-content/10 rounded-2xl hover:bg-base-100/80 hover:border-primary/30 hover:shadow-xl transition-all duration-300 hover:-translate-y-2">
                                    <div class="w-14 h-14 bg-primary/20 rounded-xl flex items-center justify-center mb-4 group-hover:bg-primary/30 transition-colors mx-auto">
                                        <x-icon name="phosphor.map-pin" class="w-7 h-7 text-primary" />
                                    </div>
                                    <h3 class="text-xl font-bold mb-2 text-center">Tracking Real-Time</h3>
                                    <p class="text-base-content/60 text-center">Monitoring posisi armada dan status pengiriman secara real-time</p>
                                </x-card>
                            </div>

                            <!-- Feature 2 -->
                            <div class="scroll-animate scale-in">
                                <x-card class="group p-6 bg-base-100/50 backdrop-blur border border-base-content/10 rounded-2xl hover:bg-base-100/80 hover:border-secondary/30 hover:shadow-xl transition-all duration-300 hover:-translate-y-2">
                                    <div class="w-14 h-14 bg-secondary/20 rounded-xl flex items-center justify-center mb-4 group-hover:bg-secondary/30 transition-colors mx-auto">
                                        <x-icon name="phosphor.chart-line-up" class="w-7 h-7 text-secondary" />
                                    </div>
                                    <h3 class="text-xl font-bold mb-2 text-center">Laporan Analytics</h3>
                                    <p class="text-base-content/60 text-center">Dashboard analytics lengkap untuk monitoring performa operasional</p>
                                </x-card>
                            </div>

                            <!-- Feature 3 -->
                            <div class="scroll-animate fade-in-right">
                                <x-card class="group p-6 bg-base-100/50 backdrop-blur border border-base-content/10 rounded-2xl hover:bg-base-100/80 hover:border-accent/30 hover:shadow-xl transition-all duration-300 hover:-translate-y-2">
                                    <div class="w-14 h-14 bg-accent/20 rounded-xl flex items-center justify-center mb-4 group-hover:bg-accent/30 transition-colors mx-auto">
                                        <x-icon name="phosphor.shield-checkered" class="w-7 h-7 text-accent" />
                                    </div>
                                    <h3 class="text-xl font-bold mb-2 text-center">Digital Security</h3>
                                    <p class="text-base-content/60 text-center">Sistem keamanan berlapis dengan audit trail dan backup otomatis</p>
                                </x-card>
                            </div>
                        </div>
                    </div>

                    <!-- Dashboard Preview -->
                    <div class="scroll-animate scale-in skeleton">
                        <div class="relative group">
                            <!-- Glow Effect -->
                            <div class="absolute -inset-1 bg-gradient-to-r from-primary to-secondary rounded-3xl blur-lg opacity-30 group-hover:opacity-50 transition-opacity duration-300"></div>

                            <!-- Main Dashboard -->
                            <div class="relative bg-base-100 rounded-3xl p-2 shadow-2xl">
                                <div class="bg-base-200 rounded-2xl overflow-hidden">
                                    <!-- Browser Bar -->
                                    <div class="flex items-center gap-2 px-4 py-3 bg-base-300 border-b border-base-content/10">
                                        <div class="flex gap-2">
                                            <div class="w-3 h-3 bg-error rounded-full"></div>
                                            <div class="w-3 h-3 bg-warning rounded-full"></div>
                                            <div class="w-3 h-3 bg-success rounded-full"></div>
                                        </div>
                                        <div class="flex-1 flex justify-center">
                                            <div class="bg-base-100 rounded-lg px-4 py-1 text-sm text-base-content/60">
                                                {{ config('app.url') }}/app/dashboard
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Dashboard Content -->
                                    <x-card class="p-6 bg-gradient-to-br from-base-100 to-base-200 min-h-64">
                                        <img
                                            src="https://picsum.photos/1200/600?random=dashboard&blur=1"
                                            alt="Dashboard DelivTrack Preview"
                                            class="w-full h-full object-cover rounded-xl opacity-60 group-hover:opacity-80 transition-opacity duration-300"
                                        />
                                    </x-card>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Trust Stats -->
                    <div class="scroll-animate">
                        <div class="mt-16 grid grid-cols-2 md:grid-cols-4 gap-8">
                            <div class="text-center">
                                <div class="text-3xl md:text-4xl font-bold text-primary mb-2">{{ config('landingpage.stats.clients_served')}}</div>
                                <div class="text-sm text-base-content/60">Klien Aktif</div>
                            </div>
                            <div class="text-center">
                                <div class="text-3xl md:text-4xl font-bold text-secondary mb-2">{{ config('landingpage.stats.documents_processed') }}</div>
                                <div class="text-sm text-base-content/60">Surat Jalan Digital</div>
                            </div>
                            <div class="text-center">
                                <div class="text-3xl md:text-4xl font-bold text-accent mb-2">{{ config('landingpage.stats.uptime')}}</div>
                                <div class="text-sm text-base-content/60">Sistem Aktif</div>
                            </div>
                            <div class="text-center">
                                <div class="text-3xl md:text-4xl font-bold text-info mb-2">{{ config('landingpage.stats.years_experience') }}</div>
                                <div class="text-sm text-base-content/60">Tahun Berpengalaman</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Enhanced Scroll Indicator -->
            <div class="absolute bottom-8 left-1/2 transform -translate-x-1/2 z-20">
                <a href="#features" class="group flex flex-col items-center">
                    <div class="text-xs font-medium text-base-content/50 mb-2 opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                        Lihat Fitur Lengkap
                    </div>
                    <div class="w-8 h-14 border-2 border-base-content/30 rounded-full flex justify-center group-hover:border-primary/50 transition-colors duration-300">
                        <div class="w-1 h-3 bg-base-content/50 rounded-full mt-2 animate-bounce group-hover:bg-primary/70 transition-colors duration-300"></div>
                    </div>
                </a>
            </div>

            <!-- Floating Elements -->
            <div class="absolute top-20 left-10 w-4 h-4 bg-primary/20 rounded-full animate-pulse hidden lg:block"></div>
            <div class="absolute top-40 right-20 w-6 h-6 bg-secondary/20 rounded-full animate-pulse delay-1000 hidden lg:block"></div>
            <div class="absolute bottom-40 left-20 w-3 h-3 bg-accent/20 rounded-full animate-pulse delay-2000 hidden lg:block"></div>
            <div class="absolute bottom-60 right-10 w-5 h-5 bg-info/20 rounded-full animate-pulse delay-500 hidden lg:block"></div>
        </section>


        HTML;
    }
}
