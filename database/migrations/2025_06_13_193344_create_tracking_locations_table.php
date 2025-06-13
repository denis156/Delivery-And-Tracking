<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Tabel untuk menyimpan data GPS tracking real-time dari driver
     */
    public function up(): void
    {
        Schema::create('tracking_locations', function (Blueprint $table) {
            $table->id();

            // Relasi ke delivery order dan driver
            $table->foreignId('delivery_order_id')->constrained()->cascadeOnDelete()->comment('ID delivery order');
            $table->foreignId('driver_id')->constrained('users')->comment('ID driver yang melakukan tracking');

            // Koordinat GPS
            $table->decimal('latitude', 10, 8)->comment('Latitude lokasi');
            $table->decimal('longitude', 11, 8)->comment('Longitude lokasi');
            $table->decimal('accuracy', 8, 2)->nullable()->comment('Akurasi GPS dalam meter');
            $table->decimal('altitude', 8, 2)->nullable()->comment('Ketinggian dari permukaan laut (meter)');

            // Data kecepatan dan arah
            $table->decimal('speed', 8, 2)->nullable()->comment('Kecepatan dalam km/jam');
            $table->integer('heading')->nullable()->comment('Arah kompas (0-360 derajat)');

            // Alamat dan lokasi deskriptif
            $table->string('address', 500)->nullable()->comment('Alamat lokasi (reverse geocoding)');
            $table->string('city', 100)->nullable()->comment('Kota');
            $table->string('district', 100)->nullable()->comment('Kecamatan');
            $table->string('province', 100)->nullable()->comment('Provinsi');

            // Status dan milestone
            $table->enum('location_type', [
                'start',        // Titik mulai perjalanan
                'checkpoint',   // Checkpoint dalam perjalanan
                'stop',         // Berhenti sementara
                'destination',  // Sampai tujuan
                'waypoint'      // Titik tracking biasa
            ])->default('waypoint')->comment('Jenis lokasi tracking');

            $table->boolean('is_milestone')->default(false)->comment('Apakah lokasi penting/milestone');
            $table->text('notes')->nullable()->comment('Catatan driver di lokasi ini');

            // Jarak tempuh
            $table->decimal('distance_from_start', 10, 2)->nullable()->comment('Jarak dari titik start (km)');
            $table->decimal('distance_to_destination', 10, 2)->nullable()->comment('Jarak ke tujuan (km)');

            // Data teknis perangkat
            $table->string('device_info', 255)->nullable()->comment('Info perangkat (browser/device)');
            $table->string('ip_address', 45)->nullable()->comment('IP address saat tracking');
            $table->enum('source', [
                'web_gps',      // GPS dari web browser
                'manual',       // Input manual driver
                'api'           // Dari API eksternal (future)
            ])->default('web_gps')->comment('Sumber data lokasi');

            // Battery dan signal strength (untuk mobile optimization)
            $table->integer('battery_level')->nullable()->comment('Level battery perangkat (%)');
            $table->integer('signal_strength')->nullable()->comment('Kekuatan sinyal (1-5)');

            // Timestamp dengan precision tinggi
            $table->timestamp('recorded_at')->useCurrent()->comment('Waktu recording GPS (high precision)');
            $table->timestamps();

            // Indexes untuk performance queries
            $table->index(['delivery_order_id', 'recorded_at'], 'idx_delivery_time');
            $table->index(['driver_id', 'recorded_at'], 'idx_driver_time');
            $table->index(['latitude', 'longitude'], 'idx_coordinates');
            $table->index('location_type', 'idx_location_type');
            $table->index('is_milestone', 'idx_milestone');
            $table->index('recorded_at', 'idx_recorded_time');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tracking_locations');
    }
};
