<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Tabel untuk audit trail lengkap semua perubahan status dan aktivitas
     */
    public function up(): void
    {
        Schema::create('status_histories', function (Blueprint $table) {
            $table->id();

            // Relasi ke delivery order
            $table->foreignId('delivery_order_id')->constrained()->cascadeOnDelete()->comment('ID delivery order');

            // User yang melakukan aksi
            $table->foreignId('user_id')->constrained()->comment('User yang melakukan perubahan');
            $table->string('user_role', 50)->comment('Role user saat melakukan aksi');

            // Detail perubahan status
            $table->string('status_from', 50)->nullable()->comment('Status sebelumnya');
            $table->string('status_to', 50)->comment('Status baru');

            // Jenis aktivitas/aksi
            $table->enum('action_type', [
                'created',          // Order dibuat
                'updated',          // Data diupdate
                'verified',         // Diverifikasi PR
                'assigned',         // Driver ditugaskan
                'dispatched',       // Mulai perjalanan
                'location_updated', // Update lokasi GPS
                'milestone_reached', // Mencapai milestone
                'arrived',          // Sampai tujuan
                'completed',        // Diselesaikan PG
                'cancelled',        // Dibatalkan
                'returned',         // Dikembalikan
                'discrepancy_noted', // Catat ketidaksesuaian
                'document_printed', // Cetak dokumen
                'note_added'        // Tambah catatan
            ])->comment('Jenis aksi yang dilakukan');

            // Detail perubahan
            $table->text('description')->comment('Deskripsi perubahan/aksi');
            $table->json('changes')->nullable()->comment('Detail perubahan field (JSON)');
            $table->text('notes')->nullable()->comment('Catatan tambahan dari user');

            // Lokasi saat aksi dilakukan
            $table->decimal('latitude', 10, 8)->nullable()->comment('Latitude saat aksi');
            $table->decimal('longitude', 11, 8)->nullable()->comment('Longitude saat aksi');
            $table->string('location_address', 500)->nullable()->comment('Alamat lokasi saat aksi');

            // Data teknis
            $table->string('ip_address', 45)->nullable()->comment('IP address user');
            $table->string('user_agent', 500)->nullable()->comment('Browser/device info');
            $table->string('device_type', 50)->nullable()->comment('Jenis device (desktop/mobile)');

            // Metadata untuk referensi
            $table->string('reference_id', 100)->nullable()->comment('ID referensi (misal: item_id jika aksi terkait item)');
            $table->string('reference_type', 100)->nullable()->comment('Tipe referensi (misal: item, document)');

            // Flag untuk notifikasi
            $table->boolean('requires_notification')->default(false)->comment('Apakah perlu kirim notifikasi');
            $table->boolean('is_critical')->default(false)->comment('Apakah aksi ini critical/penting');
            $table->boolean('is_visible_to_client')->default(true)->comment('Visible untuk client atau tidak');

            // Timestamps
            $table->timestamp('occurred_at')->useCurrent()->comment('Waktu aksi terjadi');
            $table->timestamps();

            // Indexes untuk performance
            $table->index(['delivery_order_id', 'occurred_at'], 'idx_delivery_time');
            $table->index(['user_id', 'occurred_at'], 'idx_user_time');
            $table->index(['action_type', 'occurred_at'], 'idx_action_time');
            $table->index(['status_to', 'occurred_at'], 'idx_status_time');
            $table->index('is_critical', 'idx_critical');
            $table->index('requires_notification', 'idx_notification');
            $table->index('is_visible_to_client', 'idx_client_visible');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('status_histories');
    }
};
