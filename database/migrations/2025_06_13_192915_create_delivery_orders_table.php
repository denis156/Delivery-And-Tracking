<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Tabel utama untuk delivery orders - pengganti surat jalan digital
     */
    public function up(): void
    {
        Schema::create('delivery_orders', function (Blueprint $table) {
            $table->id();

            // Identitas order
            $table->string('order_number', 50)->unique()->comment('Nomor unik delivery order');
            $table->enum('status', [
                'draft',        // Dibuat PL, belum diverifikasi
                'verified',     // Diverifikasi PR, siap dispatch
                'dispatched',   // Driver mulai perjalanan
                'in_transit',   // Dalam perjalanan (tracking aktif)
                'arrived',      // Sampai tujuan
                'completed',    // Selesai oleh PG
                'cancelled'     // Dibatalkan
            ])->default('draft')->comment('Status delivery order');

            // Data pengirim dan penerima
            $table->string('sender_name', 255)->comment('Nama pengirim');
            $table->text('sender_address')->comment('Alamat pengirim');
            $table->string('sender_phone', 20)->nullable()->comment('Telepon pengirim');

            $table->string('recipient_name', 255)->comment('Nama penerima');
            $table->text('recipient_address')->comment('Alamat penerima');
            $table->string('recipient_phone', 20)->nullable()->comment('Telepon penerima');
            $table->string('recipient_pic', 255)->nullable()->comment('PIC di lokasi tujuan');

            // Koordinat tujuan untuk GPS
            $table->decimal('destination_latitude', 10, 8)->nullable()->comment('Latitude tujuan');
            $table->decimal('destination_longitude', 11, 8)->nullable()->comment('Longitude tujuan');

            // User assignments
            $table->foreignId('created_by')->constrained('users')->comment('PL yang membuat order');
            $table->foreignId('verified_by')->nullable()->constrained('users')->comment('PR yang verifikasi');
            $table->foreignId('driver_id')->nullable()->constrained('users')->comment('Driver yang ditugaskan');
            $table->foreignId('completed_by')->nullable()->constrained('users')->comment('PG yang menyelesaikan');

            // Timestamps dan tanggal penting
            $table->timestamp('verified_at')->nullable()->comment('Waktu verifikasi PR');
            $table->timestamp('dispatched_at')->nullable()->comment('Waktu mulai perjalanan');
            $table->timestamp('arrived_at')->nullable()->comment('Waktu sampai tujuan');
            $table->timestamp('completed_at')->nullable()->comment('Waktu penyelesaian');

            // Estimasi dan aktual
            $table->date('planned_delivery_date')->comment('Tanggal rencana pengiriman');
            $table->time('planned_delivery_time')->nullable()->comment('Waktu rencana pengiriman');
            $table->decimal('estimated_distance', 8, 2)->nullable()->comment('Estimasi jarak (km)');
            $table->decimal('actual_distance', 8, 2)->nullable()->comment('Jarak aktual (km)');

            // Catatan dan keterangan
            $table->text('notes')->nullable()->comment('Catatan tambahan');
            $table->text('delivery_notes')->nullable()->comment('Catatan pengiriman dari driver');
            $table->text('completion_notes')->nullable()->comment('Catatan penyelesaian dari PG');

            // Discrepancy handling
            $table->boolean('has_discrepancy')->default(false)->comment('Ada ketidaksesuaian barang');
            $table->text('discrepancy_notes')->nullable()->comment('Catatan ketidaksesuaian');

            // Surat jalan fisik
            $table->string('physical_document_number', 100)->nullable()->comment('Nomor surat jalan fisik');
            $table->timestamp('document_printed_at')->nullable()->comment('Waktu cetak dokumen');

            $table->timestamps();
            $table->softDeletes();

            // Indexes untuk performance
            $table->index(['status', 'created_at'], 'idx_status_created');
            $table->index(['driver_id', 'status'], 'idx_driver_status');
            $table->index(['created_by', 'created_at'], 'idx_creator_date');
            $table->index('planned_delivery_date', 'idx_planned_date');
            $table->index('order_number', 'idx_order_number');
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
