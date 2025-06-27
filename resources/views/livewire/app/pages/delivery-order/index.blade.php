<div>
    {{-- HEADER --}}
    <x-header title="Surat Jalan" subtitle="Halaman manajemen surat jalan" icon="phosphor.receipt"
        icon-classes="text-primary h-10" separator>
        <x-slot:middle class="!justify-end">
            <x-input icon="phosphor.magnifying-glass" placeholder="Cari Surat Jalan..." />
        </x-slot:middle>
        <x-slot:actions>
            <x-button label="Filter" icon="phosphor.funnel" class="btn-primary" responsive />
            <x-button label="Tambah Surat Jalan" icon="phosphor.plus" class="btn-success" responsive />
        </x-slot:actions>
    </x-header>

    {{-- STATISTICS CARDS --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-2 md:gap-4 lg:gap-4 mb-8">
        <x-stat title="Surat Jalan" description="Keseluruhan" value="44" icon="phosphor.receipt" tooltip="Hello"
            color="text-primary"
            class="bg-primary/20 hover:shadow-xl hover:shadow-primary transition-all duration-300" />

        <x-stat title="Surat Jalan" description="Minggu Ini" value="22.124" icon="phosphor.receipt" tooltip="There"
            color="text-warning"
            class="bg-warning/20 hover:shadow-xl hover:shadow-primary transition-all duration-300" />

        <x-stat title="Surat Jalan" description="Bulan Ini" value="34" icon="phosphor.receipt" tooltip="Ops!"
            color="text-info" class="bg-info/20 hover:shadow-xl hover:shadow-primary transition-all duration-300" />

        <x-stat title="Surat Jalan" description="Tahun Ini" value="22.124" icon="phosphor.receipt" tooltip="Gosh!"
            color="text-success"
            class="bg-success/20 hover:shadow-xl hover:shadow-primary transition-all duration-300" />
    </div>

    {{-- Main Content --}}
    <x-card class="p-6 shadow-md" title="Daftar Surat Jalan" subtitle="Daftar lengkap surat jalan yang telah dibuat">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-2 md:gap-4 text-xs md:text-sm lg:text-md">
            <x-card title="PT. Barraka Karya Mandiri" subtitle="No: SJ-250605-0001"
                class="bg-base-200 hover:shadow-xl hover:shadow-primary transition-all duration-300">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-2 md:gap-4">
                    <div class="flex items-start gap-2 text-base-content/50 min-w-0">
                        <x-icon name="phosphor.user" label="Klien:" class="h-4 flex-shrink-0" />
                        <span class="truncate">Ibu Riri</span>
                    </div>
                    <div class="flex items-start gap-2 text-base-content/50 min-w-0">
                        <x-icon name="phosphor.package" label="Barang:" class="h-4 flex-shrink-0" />
                        <span class="truncate">8 Item</span>
                    </div>
                    <div class="flex items-start gap-2 text-base-content/50 min-w-0">
                        <x-icon name="phosphor.truck" label="Sopir:" class="h-4 flex-shrink-0" />
                        <span class="truncate">Yanto</span>
                    </div>
                    <div class="flex items-start gap-2 text-base-content/50 min-w-0">
                        <x-icon name="phosphor.map-pin" label="Tujuan:" class="h-4 flex-shrink-0" />
                        <span class="truncate">Jalan Kijang Desa Wukusao</span>
                    </div>
                    <div class="flex col-span-full items-start gap-2 text-base-content/50 min-w-0">
                        <x-icon name="phosphor.note" label="Catatan:" class="h-4 flex-shrink-0" />
                        <div class="flex-1 min-w-0">
                            <span class="truncate block">
                                Hati-hati barang mudah terbakar
                            </span>
                        </div>
                    </div>
                    <div class="flex col-span-full items-start gap-2 text-error min-w-0 ">
                        <x-icon name="phosphor.receipt-x" label="Discrepancy:" class="h-4 flex-shrink-0" />
                        <div class="flex-1 min-w-0">
                            <span class="truncate block">
                                Ada kelebihan barang dengan total kirim 10 dan yang sampai di gudang ada 14
                            </span>
                        </div>
                    </div>
                </div>
                <x-slot:menu>
                    <div class="grid grid-cols-1 grid-rows-2 gap-2">
                        <x-badge value="Tiba di tujuan" class="badge-info badge-xs truncate" />
                        <x-badge value="Discrepancy" class="badge-error badge-outline badge-xs truncate" />
                    </div>
                </x-slot:menu>
                <x-slot:actions separator>
                    <x-button label="Lihat" class="btn-info btn-md" icon="phosphor.eye" responsive />
                    <x-button label="Hapus" class="btn-error btn-md" icon="phosphor.trash" responsive />
                </x-slot:actions>
            </x-card>

        </div>
    </x-card>
</div>
