<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Tabel untuk menyimpan item/barang dalam setiap delivery order
     */
    public function up(): void
    {
        Schema::create('items', function (Blueprint $table) {
            $table->id();

            // Relasi ke delivery order
            $table->foreignId('delivery_order_id')->constrained()->cascadeOnDelete()->comment('ID delivery order');

            // Detail barang
            $table->string('name', 255)->comment('Nama barang');
            $table->text('description')->nullable()->comment('Deskripsi detail barang');
            $table->string('category', 100)->nullable()->comment('Kategori barang');
            $table->string('unit', 50)->default('pcs')->comment('Satuan (pcs, kg, liter, dll)');

            // Quantity - planned vs actual
            $table->decimal('planned_quantity', 10, 2)->comment('Jumlah yang direncanakan');
            $table->decimal('actual_quantity', 10, 2)->nullable()->comment('Jumlah aktual yang diterima');

            // Berat dan dimensi
            $table->decimal('weight', 8, 2)->nullable()->comment('Berat per unit (kg)');
            $table->decimal('length', 8, 2)->nullable()->comment('Panjang (cm)');
            $table->decimal('width', 8, 2)->nullable()->comment('Lebar (cm)');
            $table->decimal('height', 8, 2)->nullable()->comment('Tinggi (cm)');

            // Nilai dan asuransi
            $table->decimal('unit_value', 12, 2)->nullable()->comment('Nilai per unit (Rupiah)');
            $table->decimal('total_value', 12, 2)->nullable()->comment('Total nilai barang (Rupiah)');
            $table->boolean('is_insured')->default(false)->comment('Barang diasuransikan');

            // Kondisi dan handling
            $table->enum('condition_sent', ['baik', 'rusak_ringan', 'rusak_berat'])->default('baik')->comment('Kondisi saat dikirim');
            $table->enum('condition_received', ['baik', 'rusak_ringan', 'rusak_berat'])->nullable()->comment('Kondisi saat diterima');
            $table->boolean('is_fragile')->default(false)->comment('Barang mudah pecah');
            $table->boolean('requires_cold_storage')->default(false)->comment('Butuh penyimpanan dingin');

            // Status dan catatan
            $table->enum('status', [
                'prepared',     // Disiapkan untuk pengiriman
                'loaded',       // Dimuat ke kendaraan
                'in_transit',   // Dalam perjalanan
                'delivered',    // Berhasil diterima
                'damaged',      // Rusak/hilang
                'returned'      // Dikembalikan
            ])->default('prepared')->comment('Status item');

            $table->text('notes')->nullable()->comment('Catatan khusus item');
            $table->text('damage_notes')->nullable()->comment('Catatan kerusakan jika ada');

            // Barcode/SKU untuk tracking
            $table->string('barcode', 100)->nullable()->comment('Barcode/SKU barang');
            $table->string('serial_number', 100)->nullable()->comment('Serial number jika ada');

            // Urutan item dalam delivery order
            $table->integer('sort_order')->default(0)->comment('Urutan item dalam list');

            $table->timestamps();
            $table->softDeletes();

            // Indexes untuk performance
            $table->index(['delivery_order_id', 'sort_order'], 'idx_delivery_sort');
            $table->index(['delivery_order_id', 'status'], 'idx_delivery_status');
            $table->index('barcode', 'idx_barcode');
            $table->index('category', 'idx_category');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('items');
    }
};
