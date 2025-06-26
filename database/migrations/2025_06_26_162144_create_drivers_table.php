<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Tabel untuk data detail driver
     */
    public function up(): void
    {
        Schema::create('drivers', function (Blueprint $table) {
            $table->id();

            // Relasi ke user dengan role driver
            $table->foreignId('user_id')->constrained()->cascadeOnDelete()->comment('Relasi ke tabel users');

            // Data driver
            $table->string('license_number', 50)->unique()->comment('Nomor SIM driver');
            $table->enum('license_type', ['A', 'B1', 'B2', 'D', 'A UMUM', 'B1 UMUM', 'B2 UMUM'])->comment('Jenis SIM');
            $table->date('license_expiry')->comment('Tanggal kadaluarsa SIM');
            $table->string('phone', 20)->comment('Nomor telepon driver');
            $table->text('address')->nullable()->comment('Alamat driver');

            // Vehicle information
            $table->string('vehicle_type', 100)->nullable()->comment('Jenis kendaraan');
            $table->string('vehicle_plate', 20)->nullable()->comment('Plat nomor kendaraan');

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('license_number');
            $table->index('phone');
            $table->unique('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('drivers');
    }
};
