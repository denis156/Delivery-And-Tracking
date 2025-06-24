<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Tabel untuk menyimpan item atau barang dalam delivery order - simplified MVP version
     */
    public function up(): void
    {
        Schema::create('items', function (Blueprint $table) {
            $table->id();

            // Relasi ke delivery order
            $table->foreignId('delivery_order_id')->constrained()->cascadeOnDelete()->comment('Relasi ke delivery order');

            // Detail barang - simplified
            $table->string('name', 255)->comment('Nama barang');
            $table->text('description')->nullable()->comment('Deskripsi detail barang');
            $table->string('category', 100)->nullable()->comment('Kategori barang');
            $table->string('unit', 50)->default('pcs')->comment('Satuan seperti pcs atau kg atau liter');

            // Quantity - yang dikirim vs yang diterima
            $table->decimal('sent_quantity', 10, 2)->comment('Jumlah yang dikirim dari pengirim');
            $table->decimal('received_quantity', 10, 2)->nullable()->comment('Jumlah yang diterima di tujuan');

            // Physical properties - simplified (hanya weight yang essential)
            $table->decimal('weight', 8, 2)->nullable()->comment('Berat per unit dalam kilogram');

            // Value - simplified
            $table->decimal('unit_value', 12, 2)->nullable()->comment('Nilai per unit dalam rupiah');
            $table->decimal('total_value', 12, 2)->nullable()->comment('Total nilai barang dalam rupiah');

            // Status tracking - simplified
            $table->enum('status', [
                'prepared',     // Disiapkan untuk pengiriman
                'loaded',       // Dimuat ke kendaraan
                'delivered',    // Berhasil diterima
                'damaged',      // Rusak atau hilang
                'returned'      // Dikembalikan
            ])->default('prepared')->comment('Status item');

            // Condition tracking - simplified
            $table->enum('condition', [
                'baik',
                'rusak_ringan',
                'rusak_berat'
            ])->default('baik')->comment('Kondisi barang');

            // Notes
            $table->text('notes')->nullable()->comment('Catatan khusus item');
            $table->text('discrepancy_notes')->nullable()->comment('Catatan discrepancy jika jumlah kirim tidak sama dengan terima');

            // Barcode tracking
            $table->string('barcode_item', 100)->nullable()->comment('Barcode item individual');

            // Sorting
            $table->integer('sort_order')->default(0)->comment('Urutan item dalam delivery order');

            $table->timestamps();
            $table->softDeletes();

            // Indexes untuk performance
            $table->index(['delivery_order_id', 'sort_order'], 'idx_delivery_sort');
            $table->index(['delivery_order_id', 'status'], 'idx_delivery_status');
            $table->index('barcode_item', 'idx_barcode_item');
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
