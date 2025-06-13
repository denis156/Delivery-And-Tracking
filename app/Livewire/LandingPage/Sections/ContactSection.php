<?php

namespace App\Livewire\LandingPage\Sections;

use Livewire\Component;

class ContactSection extends Component
{
    public function render()
    {
        return <<<'HTML'
        <section id="contact" class="relative py-20 bg-gradient-to-br from-base-100 via-base-200 to-base-300 overflow-hidden">

            <!-- Background Effects -->
            <div class="absolute inset-0 bg-grid-pattern opacity-5"></div>
            <div class="absolute top-0 left-0 w-96 h-96 bg-primary/10 rounded-full blur-3xl -translate-x-1/2 -translate-y-1/2"></div>
            <div class="absolute bottom-0 right-0 w-96 h-96 bg-secondary/10 rounded-full blur-3xl translate-x-1/2 translate-y-1/2"></div>
            <div class="absolute top-1/2 right-1/4 w-64 h-64 bg-accent/5 rounded-full blur-2xl"></div>

            <div class="relative z-10 max-w-7xl mx-auto px-4">
                <!-- Section Header -->
                <div class="text-center mb-16">
                    <div class="scroll-animate fade-in-down">
                        <div class="gap-2 badge badge-xl badge-outline badge-primary bg-primary/20 mb-6">
                            <div aria-label="info" class="status status-primary status-md animate-pulse"></div>
                            <span class="text-sm font-medium text-primary">Contact Us</span>
                        </div>
                    </div>

                    <div class="scroll-animate">
                        <h2 class="text-4xl md:text-6xl font-bold mb-6 leading-tight">
                            <span class="block text-base-content">Hubungi</span>
                            <span class="block bg-gradient-to-r from-primary via-secondary to-accent bg-clip-text text-transparent">
                                Kami Sekarang !
                            </span>
                        </h2>
                        <p class="text-lg md:text-xl text-base-content/70 max-w-3xl mx-auto">
                            Siap memulai transformasi digital operasi transportasi Anda? Tim ahli kami siap membantu
                        </p>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 mb-20">
                    <!-- Contact Information -->
                    <div class="space-y-8">
                        <!-- Company Info Card -->
                        <div class="scroll-animate fade-in-left">
                            <div class="group relative">
                                <div class="absolute -inset-0.5 bg-gradient-to-r from-primary to-secondary rounded-2xl blur opacity-20 group-hover:opacity-40 transition-opacity duration-300"></div>

                                <x-card class="relative bg-base-100/90 backdrop-blur border border-base-content/10 rounded-2xl p-8 hover:bg-base-100 transition-all duration-300">
                                    <div class="flex items-start space-x-4 mb-6">
                                        <div class="w-16 h-16 bg-gradient-to-br from-primary/20 to-primary/10 rounded-2xl flex items-center justify-center flex-shrink-0">
                                            <x-icon name="phosphor.building-office" class="w-8 h-8 text-primary" />
                                        </div>
                                        <div>
                                            <h3 class="text-2xl font-bold text-base-content mb-2">{{ config('landingpage.company_name') }}</h3>
                                            <p class="text-base-content/70">{{ config('landingpage.company_tagline') }}</p>
                                        </div>
                                    </div>

                                    <div class="space-y-6">
                                        <!-- Address -->
                                        <div class="flex items-start space-x-4">
                                            <div class="w-12 h-12 bg-primary/10 rounded-xl flex items-center justify-center flex-shrink-0">
                                                <x-icon name="phosphor.map-pin" class="w-6 h-6 text-primary" />
                                            </div>
                                            <div>
                                                <h4 class="font-semibold text-base-content mb-1">Alamat Kantor</h4>
                                                <p class="text-sm text-base-content/70 leading-relaxed">
                                                    {{ config('landingpage.contact.address.street') }}<br>
                                                    {{ config('landingpage.contact.address.city') }}, {{ config('landingpage.contact.address.province') }}<br>
                                                    {{ config('landingpage.contact.address.postal_code') }}, {{ config('landingpage.contact.address.country') }}
                                                </p>
                                            </div>
                                        </div>

                                        <!-- Phone -->
                                        <div class="flex items-center space-x-4">
                                            <div class="w-12 h-12 bg-secondary/10 rounded-xl flex items-center justify-center flex-shrink-0">
                                                <x-icon name="phosphor.phone" class="w-6 h-6 text-secondary" />
                                            </div>
                                            <div>
                                                <h4 class="font-semibold text-base-content mb-1">Telepon</h4>
                                                <a href="tel:{{ config('landingpage.contact.phone.primary') }}"
                                                   class="text-secondary hover:text-secondary/80 transition-colors font-medium">
                                                    {{ config('landingpage.contact.phone.primary') }}
                                                </a>
                                            </div>
                                        </div>

                                        <!-- Email -->
                                        <div class="flex items-center space-x-4">
                                            <div class="w-12 h-12 bg-accent/10 rounded-xl flex items-center justify-center flex-shrink-0">
                                                <x-icon name="phosphor.envelope" class="w-6 h-6 text-accent" />
                                            </div>
                                            <div>
                                                <h4 class="font-semibold text-base-content mb-1">Email</h4>
                                                <a href="mailto:{{ config('landingpage.contact.email.info') }}"
                                                   class="text-accent hover:text-accent/80 transition-colors font-medium">
                                                    {{ config('landingpage.contact.email.info') }}
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </x-card>
                            </div>
                        </div>

                        <!-- Working Hours Card -->
                        <div class="scroll-animate fade-in-left">
                            <div class="group relative">
                                <div class="absolute -inset-0.5 bg-gradient-to-r from-accent to-info rounded-2xl blur opacity-20 group-hover:opacity-40 transition-opacity duration-300"></div>

                                <x-card class="relative bg-base-100/90 backdrop-blur border border-base-content/10 rounded-2xl p-6 hover:bg-base-100 transition-all duration-300">
                                    <div class="flex items-center space-x-3 mb-4">
                                        <div class="w-12 h-12 bg-info/10 rounded-xl flex items-center justify-center">
                                            <x-icon name="phosphor.clock" class="w-6 h-6 text-info" />
                                        </div>
                                        <h3 class="text-xl font-bold">Jam Operasional</h3>
                                    </div>

                                    <div class="space-y-3">
                                        <div class="flex justify-between items-center py-2 border-b border-base-content/5">
                                            <span class="text-base-content/70">Senin - Jumat:</span>
                                            <span class="font-medium text-info">{{ config('landingpage.contact.working_hours.weekdays') }}</span>
                                        </div>
                                        <div class="flex justify-between items-center py-2 border-b border-base-content/5">
                                            <span class="text-base-content/70">Sabtu:</span>
                                            <span class="font-medium text-warning">{{ config('landingpage.contact.working_hours.saturday') }}</span>
                                        </div>
                                        <div class="flex justify-between items-center py-2">
                                            <span class="text-base-content/70">Minggu:</span>
                                            <span class="font-medium text-error">{{ config('landingpage.contact.working_hours.sunday') }}</span>
                                        </div>
                                    </div>
                                </x-card>
                            </div>
                        </div>
                    </div>

                    <!-- Contact Form -->
                    <div class="scroll-animate fade-in-right">
                        <div class="group relative">
                            <div class="absolute -inset-0.5 bg-gradient-to-r from-secondary to-accent rounded-2xl blur opacity-20 group-hover:opacity-40 transition-opacity duration-300"></div>

                            <x-card class="relative bg-base-100/90 backdrop-blur border border-base-content/10 rounded-2xl p-8 hover:bg-base-100 transition-all duration-300">
                                <div class="flex items-center space-x-3 mb-6">
                                    <div class="w-12 h-12 bg-secondary/10 rounded-xl flex items-center justify-center">
                                        <x-icon name="phosphor.paper-plane-tilt" class="w-6 h-6 text-secondary" />
                                    </div>
                                    <div>
                                        <h3 class="text-2xl font-bold">Kirim Pesan</h3>
                                        <p class="text-base-content/60 text-sm">Isi form di bawah ini dan tim kami akan menghubungi Anda dalam 24 jam</p>
                                    </div>
                                </div>

                                <x-form wire:submit="sendMessage" no-separator>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <x-input
                                            label="Nama Lengkap"
                                            wire:model="name"
                                            placeholder="Masukkan nama lengkap Anda"
                                            required />
                                        <x-input
                                            label="Email"
                                            type="email"
                                            wire:model="email"
                                            placeholder="email@domain.com"
                                            required />
                                    </div>

                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <x-input
                                            label="Perusahaan"
                                            wire:model="company"
                                            placeholder="Nama perusahaan Anda" />
                                        <x-input
                                            label="Nomor Telepon"
                                            wire:model="phone"
                                            placeholder="+62 xxx xxx xxxx" />
                                    </div>

                                    <x-select
                                        label="Jenis Layanan"
                                        wire:model="service_type"
                                        placeholder="Pilih jenis layanan yang diminati"
                                        :options="[
                                            ['id' => 'implementation', 'name' => 'Implementasi Sistem Baru'],
                                            ['id' => 'consultation', 'name' => 'Konsultasi & Demo'],
                                            ['id' => 'integration', 'name' => 'Integrasi Sistem Existing'],
                                            ['id' => 'support', 'name' => 'Support & Maintenance'],
                                            ['id' => 'other', 'name' => 'Lainnya']
                                        ]"
                                        option-value="id"
                                        option-label="name" />

                                    <x-textarea
                                        label="Pesan"
                                        wire:model="message"
                                        placeholder="Ceritakan kebutuhan Anda secara detail..."
                                        rows="4"
                                        hint="Jelaskan kebutuhan spesifik dan volume operasi harian Anda"
                                        required />

                                    <x-slot:actions>
                                        <x-button
                                            label="Kirim Pesan"
                                            icon="phosphor.paper-plane-tilt"
                                            class="btn-primary btn-outline shadow-lg hover:shadow-xl hover:scale-105 transition-all duration-300"
                                            type="submit"
                                            spinner="sendMessage" />
                                    </x-slot:actions>
                                </x-form>
                            </x-card>
                        </div>
                    </div>
                </div>

                <!-- Statistics Section -->
                <div class="scroll-animate scale-in mb-16">
                    <div class="relative group">
                        <div class="absolute -inset-1 bg-gradient-to-r from-primary to-secondary rounded-3xl blur-lg opacity-20 group-hover:opacity-30 transition-opacity duration-300"></div>

                        <x-card class="relative bg-base-100/90 backdrop-blur border border-base-content/10 rounded-3xl p-8">
                            <div class="text-center mb-8">
                                <h3 class="text-3xl md:text-4xl font-bold mb-4">
                                    <span class="bg-gradient-to-r from-primary to-secondary bg-clip-text text-transparent">
                                        Kenapa Memilih Kami?
                                    </span>
                                </h3>
                                <p class="text-base-content/70 max-w-2xl mx-auto">
                                    Track record yang terbukti dalam transformasi digital transportasi
                                </p>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                                <!-- Stat 1 -->
                                <div class="scroll-animate fade-in-up">
                                    <div class="group relative">
                                        <div class="absolute -inset-0.5 bg-gradient-to-r from-primary to-primary/50 rounded-xl blur opacity-20 group-hover:opacity-40 transition-opacity duration-300"></div>

                                        <x-card class="relative bg-base-100/80 backdrop-blur border border-base-content/10 rounded-xl p-6 text-center hover:bg-base-100 transition-all duration-300 hover:-translate-y-1">
                                            <div class="w-12 h-12 bg-primary/10 rounded-xl flex items-center justify-center mx-auto mb-3">
                                                <x-icon name="phosphor.users-four" class="w-6 h-6 text-primary" />
                                            </div>
                                            <div class="text-2xl font-bold text-primary mb-1">{{ config('landingpage.stats.clients_count') }}</div>
                                            <div class="text-sm text-base-content/60">Klien Terpercaya</div>
                                        </x-card>
                                    </div>
                                </div>

                                <!-- Stat 2 -->
                                <div class="scroll-animate fade-in-up">
                                    <div class="group relative">
                                        <div class="absolute -inset-0.5 bg-gradient-to-r from-secondary to-secondary/50 rounded-xl blur opacity-20 group-hover:opacity-40 transition-opacity duration-300"></div>

                                        <x-card class="relative bg-base-100/80 backdrop-blur border border-base-content/10 rounded-xl p-6 text-center hover:bg-base-100 transition-all duration-300 hover:-translate-y-1">
                                            <div class="w-12 h-12 bg-secondary/10 rounded-xl flex items-center justify-center mx-auto mb-3">
                                                <x-icon name="phosphor.files" class="w-6 h-6 text-secondary" />
                                            </div>
                                            <div class="text-2xl font-bold text-secondary mb-1">{{ config('landingpage.stats.documents_processed') }}</div>
                                            <div class="text-sm text-base-content/60">Surat Jalan Diproses</div>
                                        </x-card>
                                    </div>
                                </div>

                                <!-- Stat 3 -->
                                <div class="scroll-animate fade-in-up">
                                    <div class="group relative">
                                        <div class="absolute -inset-0.5 bg-gradient-to-r from-accent to-accent/50 rounded-xl blur opacity-20 group-hover:opacity-40 transition-opacity duration-300"></div>

                                        <x-card class="relative bg-base-100/80 backdrop-blur border border-base-content/10 rounded-xl p-6 text-center hover:bg-base-100 transition-all duration-300 hover:-translate-y-1">
                                            <div class="w-12 h-12 bg-accent/10 rounded-xl flex items-center justify-center mx-auto mb-3">
                                                <x-icon name="phosphor.calendar-star" class="w-6 h-6 text-accent" />
                                            </div>
                                            <div class="text-2xl font-bold text-accent mb-1">{{ config('landingpage.stats.years_experience') }}</div>
                                            <div class="text-sm text-base-content/60">Tahun Pengalaman</div>
                                        </x-card>
                                    </div>
                                </div>

                                <!-- Stat 4 -->
                                <div class="scroll-animate fade-in-up">
                                    <div class="group relative">
                                        <div class="absolute -inset-0.5 bg-gradient-to-r from-info to-info/50 rounded-xl blur opacity-20 group-hover:opacity-40 transition-opacity duration-300"></div>

                                        <x-card class="relative bg-base-100/80 backdrop-blur border border-base-content/10 rounded-xl p-6 text-center hover:bg-base-100 transition-all duration-300 hover:-translate-y-1">
                                            <div class="w-12 h-12 bg-info/10 rounded-xl flex items-center justify-center mx-auto mb-3">
                                                <x-icon name="phosphor.clock-user" class="w-6 h-6 text-info" />
                                            </div>
                                            <div class="text-2xl font-bold text-info mb-1">{{ config('landingpage.stats.uptime') }}</div>
                                            <div class="text-sm text-base-content/60">Ketersediaan</div>
                                        </x-card>
                                    </div>
                                </div>
                            </div>
                        </x-card>
                    </div>
                </div>

                <!-- Developer Section -->
                <div class="scroll-animate scale-in">
                    <div class="relative group">
                        <div class="absolute -inset-1 bg-gradient-to-r from-accent to-primary rounded-3xl blur-lg opacity-30 group-hover:opacity-50 transition-opacity duration-300"></div>

                        <x-card class="relative bg-gradient-to-r from-accent/90 to-primary/90 backdrop-blur text-primary-content rounded-3xl p-8">
                            <div class="text-center">
                                <div class="mb-6">
                                    <h3 class="text-3xl md:text-4xl font-bold mb-4">Dikembangkan Oleh</h3>
                                    <p class="text-primary-content/80 max-w-2xl mx-auto">
                                        Tim developer berpengalaman dengan keahlian teknologi terdepan
                                    </p>
                                </div>

                                <div class="flex flex-col md:flex-row items-center justify-center space-y-4 md:space-y-0 md:space-x-6">
                                    <div class="relative group">
                                        <div class="absolute -inset-1 bg-primary-content/20 rounded-full blur opacity-50 group-hover:opacity-75 transition-opacity duration-300"></div>
                                        <x-avatar
                                            :image="'https://avatars.githubusercontent.com/u/56766058?v=4'"
                                            class="relative w-20 h-20 ring-4 ring-primary-content/30 ring-offset-2 ring-offset-transparent group-hover:scale-110 transition-transform duration-300" />
                                    </div>

                                    <div class="text-center md:text-left">
                                        <h4 class="font-bold text-2xl mb-1">{{ config('landingpage.developer.name') }}</h4>
                                        <p class="text-primary-content/70 mb-3">{{ config('landingpage.developer.role') }}</p>

                                        <div class="grid grid-cols-2 gap-2">
                                            <x-button
                                                link="{{ config('landingpage.developer.website', 'https://arteliadev.cloud') }}"
                                                external icon="phosphor.globe"
                                                class="btn-ghost btn-base-content btn-md"
                                                label="{{ config('landingpage.developer.company', 'Artelia.dev') }}"
                                                responsive
                                            />
                                            <x-button
                                                link="{{ config('landingpage.developer.github', 'https://github.com/denis156') }}"
                                                external icon="si.github"
                                                class="btn-ghost btn-base-content btn-md"
                                                label="GitHub"
                                                responsive
                                            />
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </x-card>
                    </div>
                </div>
            </div>

            <!-- Floating Elements -->
            <div class="absolute top-32 right-10 w-4 h-4 bg-primary/30 rounded-full animate-pulse hidden lg:block"></div>
            <div class="absolute top-96 left-20 w-6 h-6 bg-secondary/30 rounded-full animate-pulse delay-1000 hidden lg:block"></div>
            <div class="absolute bottom-40 right-20 w-3 h-3 bg-accent/30 rounded-full animate-pulse delay-2000 hidden lg:block"></div>
            <div class="absolute bottom-60 left-10 w-5 h-5 bg-info/30 rounded-full animate-pulse delay-500 hidden lg:block"></div>
        </section>
        HTML;
    }
}
