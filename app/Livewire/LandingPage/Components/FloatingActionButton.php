<?php

namespace App\Livewire\LandingPage\Components;

use Livewire\Component;

class FloatingActionButton extends Component
{
    public function render()
    {
        return <<<'HTML'
        <div>
            <div class="fixed bottom-6 right-6 z-40">
                <div class="dropdown dropdown-top dropdown-end">
                    <!-- Main FAB Button -->
                    <div tabindex="0" role="button" class="group relative">
                        <!-- Glow Effect -->
                        <div
                            class="absolute -inset-2 bg-gradient-to-r from-primary via-secondary to-accent rounded-full blur-lg opacity-30 group-hover:opacity-60 animate-pulse transition-opacity duration-300">
                        </div>

                        <!-- Button Container -->
                        <div
                            class="relative w-16 h-16 bg-gradient-to-br from-primary via-secondary to-accent rounded-full flex items-center justify-center shadow-2xl hover:shadow-3xl border-2 border-primary-content/20 group-hover:scale-110 transition-all duration-300 cursor-pointer">
                            <!-- Icon -->
                            <x-icon name="phosphor.chat-circle-dots"
                                class="w-8 h-8 text-primary-content group-hover:scale-110 transition-transform duration-300" />

                            <!-- Ripple Effect -->
                            <div
                                class="absolute inset-0 rounded-full bg-primary-content/10 opacity-0 group-hover:opacity-100 group-active:scale-95 transition-all duration-200">
                            </div>
                        </div>

                        <!-- Notification Badge (Optional) -->
                        <div
                            class="absolute -top-1 -right-1 w-5 h-5 bg-gradient-to-r from-error to-warning rounded-full flex items-center justify-center shadow-lg animate-bounce">
                            <span class="text-xs font-bold text-error-content">!</span>
                        </div>
                    </div>

                    <!-- Enhanced Dropdown Menu -->
                    <ul tabindex="0"
                        class="dropdown-content menu p-3 shadow-2xl bg-base-100/95 backdrop-blur-md rounded-2xl border border-base-content/10 w-56 mb-4">
                        <!-- Menu Header -->
                        <li class="menu-title mb-2">
                            <span class="flex items-center text-primary font-semibold text-sm">
                                <x-icon name="phosphor.phone-call" class="w-4 h-4 mr-2" />
                                Hubungi Kami
                            </span>
                        </li>
                        <div class="divider my-1"></div>

                        <!-- Telepon -->
                        <li>
                            <a href="tel:{{ config('landingpage.contact.phone.primary') }}"
                                class="group flex items-center space-x-3 rounded-xl hover:bg-gradient-to-r hover:from-primary/10 hover:to-primary/5 transition-all duration-300 py-3">
                                <div
                                    class="w-10 h-10 bg-gradient-to-br from-primary/20 to-primary/10 rounded-xl flex items-center justify-center group-hover:bg-primary/30 group-hover:scale-110 transition-all duration-300">
                                    <x-icon name="phosphor.phone" class="w-5 h-5 text-primary" />
                                </div>
                                <div class="flex-1">
                                    <div class="font-semibold text-base-content group-hover:text-primary transition-colors">
                                        Telepon</div>
                                    <div class="text-xs text-base-content/60">Hubungi langsung</div>
                                </div>
                                <x-icon name="phosphor.arrow-square-out"
                                    class="w-4 h-4 text-base-content/40 group-hover:text-primary group-hover:scale-110 transition-all duration-300" />
                            </a>
                        </li>

                        <!-- Email -->
                        <li>
                            <a href="mailto:{{ config('landingpage.contact.email.info') }}"
                                class="group flex items-center space-x-3 rounded-xl hover:bg-gradient-to-r hover:from-secondary/10 hover:to-secondary/5 transition-all duration-300 py-3">
                                <div
                                    class="w-10 h-10 bg-gradient-to-br from-secondary/20 to-secondary/10 rounded-xl flex items-center justify-center group-hover:bg-secondary/30 group-hover:scale-110 transition-all duration-300">
                                    <x-icon name="phosphor.envelope" class="w-5 h-5 text-secondary" />
                                </div>
                                <div class="flex-1">
                                    <div class="font-semibold text-base-content group-hover:text-secondary transition-colors">
                                        Email</div>
                                    <div class="text-xs text-base-content/60">Kirim pesan</div>
                                </div>
                                <x-icon name="phosphor.arrow-square-out"
                                    class="w-4 h-4 text-base-content/40 group-hover:text-secondary group-hover:scale-110 transition-all duration-300" />
                            </a>
                        </li>

                        <!-- WhatsApp (Optional) -->
                        <li>
                            <a href="https://wa.me/{{ str_replace(['+', ' ', '-'], '', config('landingpage.contact.phone.primary')) }}?text=Halo, saya tertarik dengan sistem tracking transportasi Anda"
                                target="_blank"
                                class="group flex items-center space-x-3 rounded-xl hover:bg-gradient-to-r hover:from-success/10 hover:to-success/5 transition-all duration-300 py-3">
                                <div
                                    class="w-10 h-10 bg-gradient-to-br from-success/20 to-success/10 rounded-xl flex items-center justify-center group-hover:bg-success/30 group-hover:scale-110 transition-all duration-300">
                                    <x-icon name="phosphor.whatsapp-logo" class="w-5 h-5 text-success" />
                                </div>
                                <div class="flex-1">
                                    <div class="font-semibold text-base-content group-hover:text-success transition-colors">
                                        WhatsApp</div>
                                    <div class="text-xs text-base-content/60">Chat langsung</div>
                                </div>
                                <x-icon name="phosphor.arrow-square-out"
                                    class="w-4 h-4 text-base-content/40 group-hover:text-success group-hover:scale-110 transition-all duration-300" />
                            </a>
                        </li>

                        <!-- Lokasi/Kontak -->
                        <li>
                            <a href="#contact"
                                class="group flex items-center space-x-3 rounded-xl hover:bg-gradient-to-r hover:from-accent/10 hover:to-accent/5 transition-all duration-300 py-3">
                                <div
                                    class="w-10 h-10 bg-gradient-to-br from-accent/20 to-accent/10 rounded-xl flex items-center justify-center group-hover:bg-accent/30 group-hover:scale-110 transition-all duration-300">
                                    <x-icon name="phosphor.map-pin" class="w-5 h-5 text-accent" />
                                </div>
                                <div class="flex-1">
                                    <div class="font-semibold text-base-content group-hover:text-accent transition-colors">
                                        Lokasi</div>
                                    <div class="text-xs text-base-content/60">Lihat alamat</div>
                                </div>
                                <x-icon name="phosphor.arrow-right"
                                    class="w-4 h-4 text-base-content/40 group-hover:text-accent group-hover:scale-110 transition-all duration-300" />
                            </a>
                        </li>

                        <!-- Menu Footer -->
                        <div class="divider my-1"></div>
                        <li class="text-center">
                            <div class="text-xs text-base-content/50 py-1">
                                <x-icon name="phosphor.clock" class="w-3 h-3 inline mr-1" />
                                Respon dalam 24 jam
                            </div>
                        </li>
                    </ul>
                </div>

                <!-- Helper Text (shows on first visit) -->
                <div
                    class="absolute bottom-full right-0 mb-4 group-hover:opacity-100 opacity-0 transition-opacity duration-300 pointer-events-none">
                    <div
                        class="bg-base-100/95 backdrop-blur-sm text-base-content text-sm px-3 py-2 rounded-lg shadow-lg border border-base-content/10 whitespace-nowrap">
                        <div class="flex items-center space-x-2">
                            <x-icon name="phosphor.info" class="w-4 h-4 text-info" />
                            <span>Butuh bantuan? Klik disini!</span>
                        </div>
                        <!-- Arrow pointer -->
                        <div
                            class="absolute top-full right-4 w-0 h-0 border-l-4 border-r-4 border-t-4 border-transparent border-t-base-100/95">
                        </div>
                    </div>
                </div>
            </div>
        </div>
        HTML;
    }
}
