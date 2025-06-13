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
                            <span class="text-sm font-medium text-info">Workflow Process</span>
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
                            Alur kerja yang terstruktur dan efisien untuk mengoptimalkan operasi transportasi
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
                                                Petugas Lapangan menginput data surat jalan dan melakukan validasi dokumen
                                            </p>
                                            <div class="flex justify-center mt-auto">
                                                <span class="px-3 py-1 bg-primary/20 text-primary rounded-full text-xs font-medium">Petugas Lapangan</span>
                                            </div>
                                        </div>
                                    </x-card>
                                </div>
                            </div>

                            <!-- Step 2: Assign ke Sopir -->
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
                                            <h4 class="font-bold text-lg mb-3">Assign ke Sopir</h4>
                                            <p class="text-base-content/70 text-sm mb-4 leading-relaxed flex-grow">
                                                Sistem mengassign surat jalan ke sopir yang sesuai berdasarkan rute
                                            </p>
                                            <div class="flex justify-center mt-auto">
                                                <span class="px-3 py-1 bg-secondary/20 text-secondary rounded-full text-xs font-medium">Sopir</span>
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
                                            <h4 class="font-bold text-lg mb-3">Real-time Tracking</h4>
                                            <p class="text-base-content/70 text-sm mb-4 leading-relaxed flex-grow">
                                                Sopir melakukan perjalanan dengan tracking GPS yang akurat dan real-time
                                            </p>
                                            <div class="flex justify-center mt-auto">
                                                <span class="px-3 py-1 bg-accent/20 text-accent rounded-full text-xs font-medium">GPS Tracking</span>
                                            </div>
                                        </div>
                                    </x-card>
                                </div>
                            </div>

                            <!-- Step 4: Konfirmasi Penerimaan -->
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
                                            <h4 class="font-bold text-lg mb-3">Konfirmasi Penerimaan</h4>
                                            <p class="text-base-content/70 text-sm mb-4 leading-relaxed flex-grow">
                                                Petugas Gudang mengkonfirmasi penerimaan dan verifikasi barang
                                            </p>
                                            <div class="flex justify-center mt-auto">
                                                <span class="px-3 py-1 bg-info/20 text-info rounded-full text-xs font-medium">Petugas Gudang</span>
                                            </div>
                                        </div>
                                    </x-card>
                                </div>
                            </div>

                            <!-- Step 5: Status Update -->
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
                                            <h4 class="font-bold text-lg mb-3">Status Update</h4>
                                            <p class="text-base-content/70 text-sm mb-4 leading-relaxed flex-grow">
                                                Sistem mengupdate status final dan generate laporan otomatis
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
                                    Akses Berdasarkan Role
                                </span>
                            </h3>
                            <p class="text-base-content/70 max-w-2xl mx-auto">
                                Setiap role memiliki akses dan fungsi yang berbeda sesuai dengan tanggung jawab masing-masing
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
                                            Oversight seluruh operasi, monitoring dashboard, dan analisis performa sistem
                                        </p>

                                        <div class="space-y-3 mt-auto">
                                            <div class="flex items-center text-sm text-base-content/60">
                                                <x-icon name="phosphor.check" class="w-4 h-4 mr-2 text-success flex-shrink-0" />
                                                <span>Dashboard monitoring</span>
                                            </div>
                                            <div class="flex items-center text-sm text-base-content/60">
                                                <x-icon name="phosphor.check" class="w-4 h-4 mr-2 text-success flex-shrink-0" />
                                                <span>Laporan lengkap</span>
                                            </div>
                                            <div class="flex items-center text-sm text-base-content/60">
                                                <x-icon name="phosphor.check" class="w-4 h-4 mr-2 text-success flex-shrink-0" />
                                                <span>Manajemen user</span>
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

                                        <h4 class="text-xl font-bold text-center mb-3">Client</h4>
                                        <p class="text-base-content/70 text-sm text-center mb-6 leading-relaxed flex-grow">
                                            Melihat daftar surat jalan miliknya dan tracking sopir real-time
                                        </p>

                                        <div class="space-y-3 mt-auto">
                                            <div class="flex items-center text-sm text-base-content/60">
                                                <x-icon name="phosphor.check" class="w-4 h-4 mr-2 text-success flex-shrink-0" />
                                                <span>Tracking real-time</span>
                                            </div>
                                            <div class="flex items-center text-sm text-base-content/60">
                                                <x-icon name="phosphor.check" class="w-4 h-4 mr-2 text-success flex-shrink-0" />
                                                <span>Status pengiriman</span>
                                            </div>
                                            <div class="flex items-center text-sm text-base-content/60">
                                                <x-icon name="phosphor.check" class="w-4 h-4 mr-2 text-success flex-shrink-0" />
                                                <span>Riwayat pengiriman</span>
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
                                            Input & validasi surat jalan, cetak fisik untuk sopir
                                        </p>

                                        <div class="space-y-3 mt-auto">
                                            <div class="flex items-center text-sm text-base-content/60">
                                                <x-icon name="phosphor.check" class="w-4 h-4 mr-2 text-success flex-shrink-0" />
                                                <span>Input data</span>
                                            </div>
                                            <div class="flex items-center text-sm text-base-content/60">
                                                <x-icon name="phosphor.check" class="w-4 h-4 mr-2 text-success flex-shrink-0" />
                                                <span>Validasi dokumen</span>
                                            </div>
                                            <div class="flex items-center text-sm text-base-content/60">
                                                <x-icon name="phosphor.check" class="w-4 h-4 mr-2 text-success flex-shrink-0" />
                                                <span>Cetak surat jalan</span>
                                            </div>
                                        </div>
                                    </x-card>
                                </div>
                            </div>

                            <!-- Sopir -->
                            <div class="scroll-animate fade-in-left h-full">
                                <div class="group relative h-full">
                                    <div class="absolute -inset-0.5 bg-gradient-to-r from-info to-success rounded-2xl blur opacity-20 group-hover:opacity-40 transition-opacity duration-300"></div>

                                    <x-card class="relative bg-base-100/90 backdrop-blur border border-base-content/10 rounded-2xl p-6 hover:bg-base-100 transition-all duration-300 hover:-translate-y-2 h-full flex flex-col">
                                        <div class="w-16 h-16 bg-gradient-to-br from-info/20 to-info/10 rounded-2xl flex items-center justify-center mx-auto mb-4">
                                            <x-icon name="phosphor.truck-trailer" class="w-8 h-8 text-info" />
                                        </div>

                                        <h4 class="text-xl font-bold text-center mb-3">Sopir</h4>
                                        <p class="text-base-content/70 text-sm text-center mb-6 leading-relaxed flex-grow">
                                            Melihat surat jalan yang dibawa, update lokasi real-time
                                        </p>

                                        <div class="space-y-3 mt-auto">
                                            <div class="flex items-center text-sm text-base-content/60">
                                                <x-icon name="phosphor.check" class="w-4 h-4 mr-2 text-success flex-shrink-0" />
                                                <span>Surat jalan aktif</span>
                                            </div>
                                            <div class="flex items-center text-sm text-base-content/60">
                                                <x-icon name="phosphor.check" class="w-4 h-4 mr-2 text-success flex-shrink-0" />
                                                <span>GPS tracking</span>
                                            </div>
                                            <div class="flex items-center text-sm text-base-content/60">
                                                <x-icon name="phosphor.check" class="w-4 h-4 mr-2 text-success flex-shrink-0" />
                                                <span>Update status</span>
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
                                            Monitoring dan koordinasi operasional dari ruang kontrol
                                        </p>

                                        <div class="space-y-3 mt-auto">
                                            <div class="flex items-center text-sm text-base-content/60">
                                                <x-icon name="phosphor.check" class="w-4 h-4 mr-2 text-success flex-shrink-0" />
                                                <span>Monitoring real-time</span>
                                            </div>
                                            <div class="flex items-center text-sm text-base-content/60">
                                                <x-icon name="phosphor.check" class="w-4 h-4 mr-2 text-success flex-shrink-0" />
                                                <span>Koordinasi operasi</span>
                                            </div>
                                            <div class="flex items-center text-sm text-base-content/60">
                                                <x-icon name="phosphor.check" class="w-4 h-4 mr-2 text-success flex-shrink-0" />
                                                <span>Laporan harian</span>
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
                                            Terima surat jalan fisik, update status hingga selesai
                                        </p>

                                        <div class="space-y-3 mt-auto">
                                            <div class="flex items-center text-sm text-base-content/60">
                                                <x-icon name="phosphor.check" class="w-4 h-4 mr-2 text-success flex-shrink-0" />
                                                <span>Penerimaan dokumen</span>
                                            </div>
                                            <div class="flex items-center text-sm text-base-content/60">
                                                <x-icon name="phosphor.check" class="w-4 h-4 mr-2 text-success flex-shrink-0" />
                                                <span>Verifikasi barang</span>
                                            </div>
                                            <div class="flex items-center text-sm text-base-content/60">
                                                <x-icon name="phosphor.check" class="w-4 h-4 mr-2 text-success flex-shrink-0" />
                                                <span>Update status final</span>
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
                                    Sistem yang dirancang untuk meminimalkan intervensi manual dan memaksimalkan efisiensi
                                </p>
                            </div>

                            <!-- Automation Features -->
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <div class="text-center group">
                                    <div class="w-12 h-12 bg-primary/10 rounded-xl flex items-center justify-center mx-auto mb-3 group-hover:bg-primary/20 transition-colors">
                                        <x-icon name="phosphor.robot" class="w-6 h-6 text-primary" />
                                    </div>
                                    <h4 class="font-semibold mb-2">Auto Assignment</h4>
                                    <p class="text-sm text-base-content/60">Sistem otomatis assign sopir berdasarkan rute optimal</p>
                                </div>

                                <div class="text-center group">
                                    <div class="w-12 h-12 bg-secondary/10 rounded-xl flex items-center justify-center mx-auto mb-3 group-hover:bg-secondary/20 transition-colors">
                                        <x-icon name="phosphor.bell-ringing" class="w-6 h-6 text-secondary" />
                                    </div>
                                    <h4 class="font-semibold mb-2">Smart Notifications</h4>
                                    <p class="text-sm text-base-content/60">Notifikasi real-time untuk setiap perubahan status</p>
                                </div>

                                <div class="text-center group">
                                    <div class="w-12 h-12 bg-accent/10 rounded-xl flex items-center justify-center mx-auto mb-3 group-hover:bg-accent/20 transition-colors">
                                        <x-icon name="phosphor.chart-line-up" class="w-6 h-6 text-accent" />
                                    </div>
                                    <h4 class="font-semibold mb-2">Auto Reports</h4>
                                    <p class="text-sm text-base-content/60">Generate laporan otomatis setiap akhir proses</p>
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
