<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Tabel utama untuk delivery orders - surat jalan digital yang simplified
     */
    public function up(): void
    {
        Schema::create('delivery_orders', function (Blueprint $table) {
            $table->id();

            // Identitas order
            $table->string('order_number', 50)->unique()->comment('Nomor unik delivery order');
            $table->string('barcode_do', 100)->unique()->comment('Barcode untuk delivery order');

            $table->enum('status', [
                'draft',        // Petugas lapangan buat delivery order dan pilih driver
                'loading',      // Driver loading barang, petugas lapangan input items
                'verified',     // Petugas ruangan input data tujuan dan validasi dan cetak
                'dispatched',   // Driver berangkat dengan surat jalan
                'arrived',      // Driver sampai tujuan, mulai proses pembongkaran
                'completed',    // Petugas gudang selesai pembongkaran dan validasi
                'cancelled'     // Dibatalkan
            ])->default('draft')->comment('Status delivery order');

            // Data penerima - ambil dari user dengan role client
            $table->foreignId('client_id')->constrained('users')->comment('Client penerima dengan role client');
            $table->text('delivery_address')->comment('Alamat pengiriman atau tujuan');

            // Koordinat tujuan untuk sistem tracking menggunakan global positioning system
            $table->decimal('destination_latitude', 10, 8)->nullable()->comment('Latitude tujuan');
            $table->decimal('destination_longitude', 11, 8)->nullable()->comment('Longitude tujuan');

            // User assignments - sesuai workflow yang benar
            $table->foreignId('created_by')->constrained('users')->comment('Petugas lapangan yang buat delivery order dan pilih driver dan input items');
            $table->foreignId('driver_id')->nullable()->constrained('users')->comment('Driver yang dipilih petugas lapangan');
            $table->foreignId('verified_by')->nullable()->constrained('users')->comment('Petugas ruangan yang input data tujuan dan verifikasi');
            $table->foreignId('completed_by')->nullable()->constrained('users')->comment('Petugas gudang yang scan barcode dan selesaikan');

            // Key timestamps - sesuai dengan status yang ada
            $table->timestamp('loading_started_at')->nullable()->comment('Waktu mulai status loading');
            $table->timestamp('verified_at')->nullable()->comment('Waktu status verified selesai');
            $table->timestamp('dispatched_at')->nullable()->comment('Waktu status dispatched dimulai');
            $table->timestamp('arrived_at')->nullable()->comment('Waktu status arrived dimulai');
            $table->timestamp('completed_at')->nullable()->comment('Waktu status completed selesai');

            // Catatan
            $table->text('notes')->nullable()->comment('Catatan tambahan order');
            $table->text('completion_notes')->nullable()->comment('Catatan penyelesaian dari petugas gudang');

            // Discrepancy handling
            $table->boolean('has_discrepancy')->default(false)->comment('Ada ketidaksesuaian barang');
            $table->text('discrepancy_notes')->nullable()->comment('Catatan ketidaksesuaian');

            $table->timestamps();
            $table->softDeletes();

            // Indexes untuk performance
            $table->index(['status', 'created_at'], 'idx_status_created');
            $table->index(['driver_id', 'status'], 'idx_driver_status');
            $table->index(['client_id', 'created_at'], 'idx_client_date');
            $table->index(['created_by', 'created_at'], 'idx_creator_date');
            $table->index('order_number', 'idx_order_number');
            $table->index('barcode_do', 'idx_barcode_delivery_order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('delivery_orders');
    }
};
