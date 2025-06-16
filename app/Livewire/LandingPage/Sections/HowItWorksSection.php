<?php

namespace App\Livewire\LandingPage\Sections;

use Livewire\Component;

class HowItWorksSection extends Component
{
    public function render()
    {
        return <<<'HTML'
        <section id="how-it-works" class="relative py-20 bg-gradient-to-br from-base-200 via-base-300 to-base-200 overflow-hidden">

            <!-- Background Effects -->
            <div class="absolute inset-0 bg-grid-pattern opacity-5"></div>
            <div class="absolute top-0 right-0 w-96 h-96 bg-warning/10 rounded-full blur-3xl translate-x-1/2 -translate-y-1/2"></div>
            <div class="absolute bottom-0 left-0 w-96 h-96 bg-success/10 rounded-full blur-3xl -translate-x-1/2 translate-y-1/2"></div>

            <div class="relative z-10 max-w-7xl mx-auto px-4">
                <!-- Section Header -->
                <div class="text-center mb-16">
                    <div class="scroll-animate fade-in-down">
                        <div class="gap-2 badge badge-xl badge-outline badge-info bg-info/20 mb-6">
                            <div aria-label="info" class="status status-info status-md animate-pulse"></div>
                            <span class="text-sm font-medium text-info">Alur Kerja Digital</span>
                        </div>
                    </div>

                    <div class="scroll-animate">
                        <h2 class="text-4xl md:text-6xl font-bold mb-6 leading-tight">
                            <span class="block text-base-content">Cara Sistem</span>
                            <span class="block bg-gradient-to-r from-primary via-secondary to-info bg-clip-text text-transparent">
                                Bekerja
                            </span>
                        </h2>
                        <p class="text-lg md:text-xl text-base-content/70 max-w-3xl mx-auto">
                            Workflow {{ config('landingpage.stats.paperless_percentage') }} digital dengan {{ config('landingpage.features.multi_role_count') }} role
                            untuk mengoptimalkan operasi {{ config('landingpage.company_name') }} di {{ config('landingpage.business.primary_location') }}
                        </p>
                    </div>
                </div>

                <!-- Modern Timeline -->
                <div class="scroll-animate scale-in mb-20">
                    <div class="relative">
                        <!-- Background Timeline Line -->
                        <div class="absolute top-1/2 left-0 right-0 h-1 bg-gradient-to-r from-primary via-accent to-success rounded-full transform -translate-y-1/2 hidden lg:block"></div>

                        <!-- Timeline Steps -->
                        <div class="grid grid-cols-1 lg:grid-cols-5 gap-8 lg:gap-4">

                            <!-- Step 1: Input & Validasi -->
                            <div class="scroll-animate fade-in-up group h-full">
                                <div class="relative h-full flex flex-col">
                                    <!-- Step Number -->
                                    <div class="w-16 h-16 bg-gradient-to-br from-primary to-primary/80 rounded-full flex items-center justify-center mx-auto mb-4 shadow-lg group-hover:scale-110 transition-transform relative z-10">
                                        <span class="text-primary-content font-bold text-lg">1</span>
                                    </div>

                                    <!-- Step Card -->
                                    <x-card class="bg-base-100/90 backdrop-blur border border-base-content/10 rounded-2xl p-6 hover:bg-base-100 hover:shadow-xl transition-all duration-300 hover:-translate-y-2 flex-1 flex flex-col">
                                        <div class="text-center flex flex-col h-full">
                                            <div class="w-12 h-12 bg-primary/20 rounded-xl flex items-center justify-center mx-auto mb-3">
                                                <x-icon name="phosphor.file-plus" class="w-6 h-6 text-primary" />
                                            </div>
                                            <h4 class="font-bold text-lg mb-3">Input & Validasi</h4>
                                            <p class="text-base-content/70 text-sm mb-4 leading-relaxed flex-grow">
                                                Petugas Lapangan melakukan input dan validasi dokumen dengan akurasi {{ config('landingpage.stats.data_accuracy') }}
                                            </p>
                                            <div class="flex justify-center mt-auto">
                                                <span class="px-3 py-1 bg-primary/20 text-primary rounded-full text-xs font-medium">Petugas Lapangan</span>
                                            </div>
                                        </div>
                                    </x-card>
                                </div>
                            </div>

                            <!-- Step 2: Auto Assignment -->
                            <div class="scroll-animate fade-in-up group h-full">
                                <div class="relative h-full flex flex-col">
                                    <div class="w-16 h-16 bg-gradient-to-br from-secondary to-secondary/80 rounded-full flex items-center justify-center mx-auto mb-4 shadow-lg group-hover:scale-110 transition-transform relative z-10">
                                        <span class="text-secondary-content font-bold text-lg">2</span>
                                    </div>

                                    <x-card class="bg-base-100/90 backdrop-blur border border-base-content/10 rounded-2xl p-6 hover:bg-base-100 hover:shadow-xl transition-all duration-300 hover:-translate-y-2 flex-1 flex flex-col">
                                        <div class="text-center flex flex-col h-full">
                                            <div class="w-12 h-12 bg-secondary/20 rounded-xl flex items-center justify-center mx-auto mb-3">
                                                <x-icon name="phosphor.user-plus" class="w-6 h-6 text-secondary" />
                                            </div>
                                            <h4 class="font-bold text-lg mb-3">Auto Assignment</h4>
                                            <p class="text-base-content/70 text-sm mb-4 leading-relaxed flex-grow">
                                                Sistem otomatis assign pengemudi berdasarkan rute optimal untuk coverage area {{ config('landingpage.business.primary_location') }}
                                            </p>
                                            <div class="flex justify-center mt-auto">
                                                <span class="px-3 py-1 bg-secondary/20 text-secondary rounded-full text-xs font-medium">Smart Assignment</span>
                                            </div>
                                        </div>
                                    </x-card>
                                </div>
                            </div>

                            <!-- Step 3: Real-time Tracking -->
                            <div class="scroll-animate fade-in-up group h-full">
                                <div class="relative h-full flex flex-col">
                                    <div class="w-16 h-16 bg-gradient-to-br from-accent to-accent/80 rounded-full flex items-center justify-center mx-auto mb-4 shadow-lg group-hover:scale-110 transition-transform relative z-10">
                                        <span class="text-accent-content font-bold text-lg">3</span>
                                    </div>

                                    <x-card class="bg-base-100/90 backdrop-blur border border-base-content/10 rounded-2xl p-6 hover:bg-base-100 hover:shadow-xl transition-all duration-300 hover:-translate-y-2 flex-1 flex flex-col">
                                        <div class="text-center flex flex-col h-full">
                                            <div class="w-12 h-12 bg-accent/20 rounded-xl flex items-center justify-center mx-auto mb-3">
                                                <x-icon name="phosphor.map-pin" class="w-6 h-6 text-accent" />
                                            </div>
                                            <h4 class="font-bold text-lg mb-3">GPS Tracking</h4>
                                            <p class="text-base-content/70 text-sm mb-4 leading-relaxed flex-grow">
                                                Monitoring {{ config('landingpage.stats.uptime') }} dengan teknologi WebSocket untuk tracking real-time di {{ config('landingpage.business.primary_location') }}
                                            </p>
                                            <div class="flex justify-center mt-auto">
                                                <span class="px-3 py-1 bg-accent/20 text-accent rounded-full text-xs font-medium">{{ config('landingpage.stats.uptime') }}</span>
                                            </div>
                                        </div>
                                    </x-card>
                                </div>
                            </div>

                            <!-- Step 4: Digital Confirmation -->
                            <div class="scroll-animate fade-in-up group h-full">
                                <div class="relative h-full flex flex-col">
                                    <div class="w-16 h-16 bg-gradient-to-br from-info to-info/80 rounded-full flex items-center justify-center mx-auto mb-4 shadow-lg group-hover:scale-110 transition-transform relative z-10">
                                        <span class="text-info-content font-bold text-lg">4</span>
                                    </div>

                                    <x-card class="bg-base-100/90 backdrop-blur border border-base-content/10 rounded-2xl p-6 hover:bg-base-100 hover:shadow-xl transition-all duration-300 hover:-translate-y-2 flex-1 flex flex-col">
                                        <div class="text-center flex flex-col h-full">
                                            <div class="w-12 h-12 bg-info/20 rounded-xl flex items-center justify-center mx-auto mb-3">
                                                <x-icon name="phosphor.check-circle" class="w-6 h-6 text-info" />
                                            </div>
                                            <h4 class="font-bold text-lg mb-3">Konfirmasi Digital</h4>
                                            <p class="text-base-content/70 text-sm mb-4 leading-relaxed flex-grow">
                                                Petugas Gudang melakukan konfirmasi penerimaan dengan sistem paperless {{ config('landingpage.stats.paperless_percentage') }}
                                            </p>
                                            <div class="flex justify-center mt-auto">
                                                <span class="px-3 py-1 bg-info/20 text-info rounded-full text-xs font-medium">Petugas Gudang</span>
                                            </div>
                                        </div>
                                    </x-card>
                                </div>
                            </div>

                            <!-- Step 5: Auto Reporting -->
                            <div class="scroll-animate fade-in-up group h-full">
                                <div class="relative h-full flex flex-col">
                                    <div class="w-16 h-16 bg-gradient-to-br from-success to-success/80 rounded-full flex items-center justify-center mx-auto mb-4 shadow-lg group-hover:scale-110 transition-transform relative z-10">
                                        <span class="text-success-content font-bold text-lg">5</span>
                                    </div>

                                    <x-card class="bg-base-100/90 backdrop-blur border border-base-content/10 rounded-2xl p-6 hover:bg-base-100 hover:shadow-xl transition-all duration-300 hover:-translate-y-2 flex-1 flex flex-col">
                                        <div class="text-center flex flex-col h-full">
                                            <div class="w-12 h-12 bg-success/20 rounded-xl flex items-center justify-center mx-auto mb-3">
                                                <x-icon name="phosphor.printer" class="w-6 h-6 text-success" />
                                            </div>
                                            <h4 class="font-bold text-lg mb-3">Auto Reporting</h4>
                                            <p class="text-base-content/70 text-sm mb-4 leading-relaxed flex-grow">
                                                Sistem generate laporan otomatis untuk {{ config('landingpage.stats.documents_processed') }} dokumen yang telah diproses
                                            </p>
                                            <div class="flex justify-center mt-auto">
                                                <span class="px-3 py-1 bg-success/20 text-success rounded-full text-xs font-medium">Completed</span>
                                            </div>
                                        </div>
                                    </x-card>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Role-based Access Grid -->
                <div class="mb-16">
                    <div class="text-center mb-12">
                        <div class="scroll-animate">
                            <h3 class="text-3xl md:text-4xl font-bold mb-4">
                                <span class="bg-gradient-to-r from-primary to-secondary bg-clip-text text-transparent">
                                    {{ config('landingpage.features.multi_role_count') }} Role Management
                                </span>
                            </h3>
                            <p class="text-base-content/70 max-w-2xl mx-auto">
                                Sistem dengan {{ config('landingpage.features.multi_role_count') }} role berbeda untuk mengoptimalkan operasi
                                {{ config('landingpage.company_name') }} dengan pengalaman {{ config('landingpage.stats.years_experience') }} tahun
                            </p>
                        </div>
                    </div>

                    <div class="scroll-animate-container">
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            <!-- Manager/Admin -->
                            <div class="scroll-animate fade-in-left h-full">
                                <div class="group relative h-full">
                                    <div class="absolute -inset-0.5 bg-gradient-to-r from-primary to-secondary rounded-2xl blur opacity-20 group-hover:opacity-40 transition-opacity duration-300"></div>

                                    <x-card class="relative bg-base-100/90 backdrop-blur border border-base-content/10 rounded-2xl p-6 hover:bg-base-100 transition-all duration-300 hover:-translate-y-2 h-full flex flex-col">
                                        <div class="w-16 h-16 bg-gradient-to-br from-primary/20 to-primary/10 rounded-2xl flex items-center justify-center mx-auto mb-4">
                                            <x-icon name="phosphor.user-circle" class="w-8 h-8 text-primary" />
                                        </div>

                                        <h4 class="text-xl font-bold text-center mb-3">Manager/Admin</h4>
                                        <p class="text-base-content/70 text-sm text-center mb-6 leading-relaxed flex-grow">
                                            Oversight penuh dengan dashboard monitoring {{ config('landingpage.stats.uptime') }}
                                            dan analisis untuk {{ config('landingpage.stats.clients_served') }} klien
                                        </p>

                                        <div class="space-y-3 mt-auto">
                                            <div class="flex items-center text-sm text-base-content/60">
                                                <x-icon name="phosphor.check" class="w-4 h-4 mr-2 text-success flex-shrink-0" />
                                                <span>Dashboard {{ config('landingpage.stats.uptime') }}</span>
                                            </div>
                                            <div class="flex items-center text-sm text-base-content/60">
                                                <x-icon name="phosphor.check" class="w-4 h-4 mr-2 text-success flex-shrink-0" />
                                                <span>Laporan Analytics</span>
                                            </div>
                                            <div class="flex items-center text-sm text-base-content/60">
                                                <x-icon name="phosphor.check" class="w-4 h-4 mr-2 text-success flex-shrink-0" />
                                                <span>User Management</span>
                                            </div>
                                        </div>
                                    </x-card>
                                </div>
                            </div>

                            <!-- Client -->
                            <div class="scroll-animate scale-in h-full">
                                <div class="group relative h-full">
                                    <div class="absolute -inset-0.5 bg-gradient-to-r from-secondary to-accent rounded-2xl blur opacity-20 group-hover:opacity-40 transition-opacity duration-300"></div>

                                    <x-card class="relative bg-base-100/90 backdrop-blur border border-base-content/10 rounded-2xl p-6 hover:bg-base-100 transition-all duration-300 hover:-translate-y-2 h-full flex flex-col">
                                        <div class="w-16 h-16 bg-gradient-to-br from-secondary/20 to-secondary/10 rounded-2xl flex items-center justify-center mx-auto mb-4">
                                            <x-icon name="phosphor.building-office" class="w-8 h-8 text-secondary" />
                                        </div>

                                        <h4 class="text-xl font-bold text-center mb-3">Client Portal</h4>
                                        <p class="text-base-content/70 text-sm text-center mb-6 leading-relaxed flex-grow">
                                            Akses real-time untuk tracking pengiriman di seluruh coverage area {{ config('landingpage.business.primary_location') }}
                                        </p>

                                        <div class="space-y-3 mt-auto">
                                            <div class="flex items-center text-sm text-base-content/60">
                                                <x-icon name="phosphor.check" class="w-4 h-4 mr-2 text-success flex-shrink-0" />
                                                <span>Real-time Tracking</span>
                                            </div>
                                            <div class="flex items-center text-sm text-base-content/60">
                                                <x-icon name="phosphor.check" class="w-4 h-4 mr-2 text-success flex-shrink-0" />
                                                <span>Status Updates</span>
                                            </div>
                                            <div class="flex items-center text-sm text-base-content/60">
                                                <x-icon name="phosphor.check" class="w-4 h-4 mr-2 text-success flex-shrink-0" />
                                                <span>History Log</span>
                                            </div>
                                        </div>
                                    </x-card>
                                </div>
                            </div>

                            <!-- Petugas Lapangan -->
                            <div class="scroll-animate fade-in-right h-full">
                                <div class="group relative h-full">
                                    <div class="absolute -inset-0.5 bg-gradient-to-r from-accent to-info rounded-2xl blur opacity-20 group-hover:opacity-40 transition-opacity duration-300"></div>

                                    <x-card class="relative bg-base-100/90 backdrop-blur border border-base-content/10 rounded-2xl p-6 hover:bg-base-100 transition-all duration-300 hover:-translate-y-2 h-full flex flex-col">
                                        <div class="w-16 h-16 bg-gradient-to-br from-accent/20 to-accent/10 rounded-2xl flex items-center justify-center mx-auto mb-4">
                                            <x-icon name="phosphor.clipboard-text" class="w-8 h-8 text-accent" />
                                        </div>

                                        <h4 class="text-xl font-bold text-center mb-3">Petugas Lapangan</h4>
                                        <p class="text-base-content/70 text-sm text-center mb-6 leading-relaxed flex-grow">
                                            Input dan validasi dengan akurasi {{ config('landingpage.stats.data_accuracy') }} untuk
                                            operasi {{ config('landingpage.company_description') }}
                                        </p>

                                        <div class="space-y-3 mt-auto">
                                            <div class="flex items-center text-sm text-base-content/60">
                                                <x-icon name="phosphor.check" class="w-4 h-4 mr-2 text-success flex-shrink-0" />
                                                <span>Input Digital</span>
                                            </div>
                                            <div class="flex items-center text-sm text-base-content/60">
                                                <x-icon name="phosphor.check" class="w-4 h-4 mr-2 text-success flex-shrink-0" />
                                                <span>Auto Validation</span>
                                            </div>
                                            <div class="flex items-center text-sm text-base-content/60">
                                                <x-icon name="phosphor.check" class="w-4 h-4 mr-2 text-success flex-shrink-0" />
                                                <span>Print Documents</span>
                                            </div>
                                        </div>
                                    </x-card>
                                </div>
                            </div>

                            <!-- Pengemudi -->
                            <div class="scroll-animate fade-in-left h-full">
                                <div class="group relative h-full">
                                    <div class="absolute -inset-0.5 bg-gradient-to-r from-info to-success rounded-2xl blur opacity-20 group-hover:opacity-40 transition-opacity duration-300"></div>

                                    <x-card class="relative bg-base-100/90 backdrop-blur border border-base-content/10 rounded-2xl p-6 hover:bg-base-100 transition-all duration-300 hover:-translate-y-2 h-full flex flex-col">
                                        <div class="w-16 h-16 bg-gradient-to-br from-info/20 to-info/10 rounded-2xl flex items-center justify-center mx-auto mb-4">
                                            <x-icon name="phosphor.truck-trailer" class="w-8 h-8 text-info" />
                                        </div>

                                        <h4 class="text-xl font-bold text-center mb-3">Pengemudi</h4>
                                        <p class="text-base-content/70 text-sm text-center mb-6 leading-relaxed flex-grow">
                                            Portal mobile untuk pengemudi dengan GPS tracking real-time dan akses surat jalan digital
                                            di {{ config('landingpage.business.primary_location') }}
                                        </p>

                                        <div class="space-y-3 mt-auto">
                                            <div class="flex items-center text-sm text-base-content/60">
                                                <x-icon name="phosphor.check" class="w-4 h-4 mr-2 text-success flex-shrink-0" />
                                                <span>Surat Jalan Mobile</span>
                                            </div>
                                            <div class="flex items-center text-sm text-base-content/60">
                                                <x-icon name="phosphor.check" class="w-4 h-4 mr-2 text-success flex-shrink-0" />
                                                <span>GPS Auto Update</span>
                                            </div>
                                            <div class="flex items-center text-sm text-base-content/60">
                                                <x-icon name="phosphor.check" class="w-4 h-4 mr-2 text-success flex-shrink-0" />
                                                <span>Status Progress</span>
                                            </div>
                                        </div>
                                    </x-card>
                                </div>
                            </div>

                            <!-- Petugas Ruangan -->
                            <div class="scroll-animate scale-in h-full">
                                <div class="group relative h-full">
                                    <div class="absolute -inset-0.5 bg-gradient-to-r from-success to-warning rounded-2xl blur opacity-20 group-hover:opacity-40 transition-opacity duration-300"></div>

                                    <x-card class="relative bg-base-100/90 backdrop-blur border border-base-content/10 rounded-2xl p-6 hover:bg-base-100 transition-all duration-300 hover:-translate-y-2 h-full flex flex-col">
                                        <div class="w-16 h-16 bg-gradient-to-br from-success/20 to-success/10 rounded-2xl flex items-center justify-center mx-auto mb-4">
                                            <x-icon name="phosphor.desktop-tower" class="w-8 h-8 text-success" />
                                        </div>

                                        <h4 class="text-xl font-bold text-center mb-3">Petugas Ruangan</h4>
                                        <p class="text-base-content/70 text-sm text-center mb-6 leading-relaxed flex-grow">
                                            Monitoring dan koordinasi operasional dari control center dengan dashboard
                                            {{ config('landingpage.stats.uptime') }} untuk {{ config('landingpage.company_name') }}
                                        </p>

                                        <div class="space-y-3 mt-auto">
                                            <div class="flex items-center text-sm text-base-content/60">
                                                <x-icon name="phosphor.check" class="w-4 h-4 mr-2 text-success flex-shrink-0" />
                                                <span>Control Center</span>
                                            </div>
                                            <div class="flex items-center text-sm text-base-content/60">
                                                <x-icon name="phosphor.check" class="w-4 h-4 mr-2 text-success flex-shrink-0" />
                                                <span>Live Monitoring</span>
                                            </div>
                                            <div class="flex items-center text-sm text-base-content/60">
                                                <x-icon name="phosphor.check" class="w-4 h-4 mr-2 text-success flex-shrink-0" />
                                                <span>Daily Reports</span>
                                            </div>
                                        </div>
                                    </x-card>
                                </div>
                            </div>

                            <!-- Petugas Gudang -->
                            <div class="scroll-animate fade-in-right h-full">
                                <div class="group relative h-full">
                                    <div class="absolute -inset-0.5 bg-gradient-to-r from-warning to-error rounded-2xl blur opacity-20 group-hover:opacity-40 transition-opacity duration-300"></div>

                                    <x-card class="relative bg-base-100/90 backdrop-blur border border-base-content/10 rounded-2xl p-6 hover:bg-base-100 transition-all duration-300 hover:-translate-y-2 h-full flex flex-col">
                                        <div class="w-16 h-16 bg-gradient-to-br from-warning/20 to-warning/10 rounded-2xl flex items-center justify-center mx-auto mb-4">
                                            <x-icon name="phosphor.warehouse" class="w-8 h-8 text-warning" />
                                        </div>

                                        <h4 class="text-xl font-bold text-center mb-3">Petugas Gudang</h4>
                                        <p class="text-base-content/70 text-sm text-center mb-6 leading-relaxed flex-grow">
                                            Konfirmasi penerimaan dengan sistem digital dan update status final untuk
                                            {{ config('landingpage.stats.documents_processed') }} dokumen yang diproses
                                        </p>

                                        <div class="space-y-3 mt-auto">
                                            <div class="flex items-center text-sm text-base-content/60">
                                                <x-icon name="phosphor.check" class="w-4 h-4 mr-2 text-success flex-shrink-0" />
                                                <span>Digital Confirmation</span>
                                            </div>
                                            <div class="flex items-center text-sm text-base-content/60">
                                                <x-icon name="phosphor.check" class="w-4 h-4 mr-2 text-success flex-shrink-0" />
                                                <span>Cargo Verification</span>
                                            </div>
                                            <div class="flex items-center text-sm text-base-content/60">
                                                <x-icon name="phosphor.check" class="w-4 h-4 mr-2 text-success flex-shrink-0" />
                                                <span>Final Status Update</span>
                                            </div>
                                        </div>
                                    </x-card>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Process Timeline Summary -->
                <div class="scroll-animate scale-in">
                    <div class="relative group">
                        <div class="absolute -inset-1 bg-gradient-to-r from-primary to-accent rounded-3xl blur-lg opacity-20 group-hover:opacity-30 transition-opacity duration-300"></div>

                        <x-card class="relative bg-base-100/90 backdrop-blur border border-base-content/10 rounded-3xl p-8">
                            <div class="text-center mb-8">
                                <h3 class="text-3xl font-bold mb-4 bg-gradient-to-r from-primary to-accent bg-clip-text text-transparent">
                                    Otomatisasi Penuh
                                </h3>
                                <p class="text-base-content/70 max-w-2xl mx-auto">
                                    Sistem {{ config('landingpage.stats.paperless_percentage') }} digital yang dirancang untuk
                                    {{ config('landingpage.company_name') }} dengan efisiensi {{ config('landingpage.stats.efficiency_improvement') }}
                                </p>
                            </div>

                            <!-- Automation Features -->
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <div class="text-center group">
                                    <div class="w-12 h-12 bg-primary/10 rounded-xl flex items-center justify-center mx-auto mb-3 group-hover:bg-primary/20 transition-colors">
                                        <x-icon name="phosphor.robot" class="w-6 h-6 text-primary" />
                                    </div>
                                    <h4 class="font-semibold mb-2">Smart Assignment</h4>
                                    <p class="text-sm text-base-content/60">Auto assign pengemudi berdasarkan rute optimal {{ config('landingpage.business.primary_location') }}</p>
                                </div>

                                <div class="text-center group">
                                    <div class="w-12 h-12 bg-secondary/10 rounded-xl flex items-center justify-center mx-auto mb-3 group-hover:bg-secondary/20 transition-colors">
                                        <x-icon name="phosphor.bell-ringing" class="w-6 h-6 text-secondary" />
                                    </div>
                                    <h4 class="font-semibold mb-2">Notifikasi Real-Time</h4>
                                    <p class="text-sm text-base-content/60">Update otomatis {{ config('landingpage.stats.uptime') }} untuk semua perubahan status</p>
                                </div>

                                <div class="text-center group">
                                    <div class="w-12 h-12 bg-accent/10 rounded-xl flex items-center justify-center mx-auto mb-3 group-hover:bg-accent/20 transition-colors">
                                        <x-icon name="phosphor.chart-line-up" class="w-6 h-6 text-accent" />
                                    </div>
                                    <h4 class="font-semibold mb-2">Auto Analytics</h4>
                                    <p class="text-sm text-base-content/60">Generate laporan dengan akurasi {{ config('landingpage.stats.data_accuracy') }} otomatis</p>
                                </div>
                            </div>

                            <!-- Additional System Info -->
                            <div class="mt-8 pt-6 border-t border-base-content/10">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div class="flex items-center space-x-3">
                                        <div class="w-10 h-10 bg-success/10 rounded-lg flex items-center justify-center">
                                            <x-icon name="phosphor.check-circle" class="w-5 h-5 text-success" />
                                        </div>
                                        <div>
                                            <h5 class="font-semibold">Pengalaman {{ config('landingpage.stats.years_experience') }} Tahun</h5>
                                            <p class="text-sm text-base-content/60">Melayani industri transportasi {{ config('landingpage.business.primary_location') }}</p>
                                        </div>
                                    </div>
                                    <div class="flex items-center space-x-3">
                                        <div class="w-10 h-10 bg-info/10 rounded-lg flex items-center justify-center">
                                            <x-icon name="phosphor.users-three" class="w-5 h-5 text-info" />
                                        </div>
                                        <div>
                                            <h5 class="font-semibold">{{ config('landingpage.stats.clients_served') }} Klien Aktif</h5>
                                            <p class="text-sm text-base-content/60">Dipercaya oleh berbagai perusahaan transportasi</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </x-card>
                    </div>
                </div>

            </div>

            <!-- Floating Elements -->
            <div class="absolute top-32 left-10 w-3 h-3 bg-info/30 rounded-full animate-pulse hidden lg:block"></div>
            <div class="absolute top-96 right-20 w-5 h-5 bg-success/30 rounded-full animate-pulse delay-1000 hidden lg:block"></div>
            <div class="absolute bottom-32 left-20 w-4 h-4 bg-warning/30 rounded-full animate-pulse delay-2000 hidden lg:block"></div>
        </section>
        HTML;
    }
}
