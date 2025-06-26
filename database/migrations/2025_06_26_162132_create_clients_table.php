<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Tabel untuk data detail client
     */
    public function up(): void
    {
        Schema::create('clients', function (Blueprint $table) {
            $table->id();

            // Relasi ke user dengan role client
            $table->foreignId('user_id')->constrained()->cascadeOnDelete()->comment('Relasi ke tabel users');

            // Data perusahaan/client
            $table->string('company_name')->comment('Nama perusahaan client');
            $table->string('company_code', 20)->unique()->comment('Kode perusahaan unik');
            $table->text('company_address')->comment('Alamat perusahaan');
            $table->string('phone', 20)->comment('Nomor telepon utama');
            $table->string('fax', 20)->nullable()->comment('Nomor fax');
            $table->string('tax_id', 30)->nullable()->comment('NPWP perusahaan');

            // Contact person
            $table->string('contact_person')->comment('Nama contact person');
            $table->string('contact_phone', 20)->comment('Telepon contact person');
            $table->string('contact_email')->comment('Email contact person');
            $table->string('contact_position', 100)->nullable()->comment('Jabatan contact person');


            // Coordinates untuk lokasi perusahaan
            $table->decimal('company_latitude', 10, 8)->nullable()->comment('Latitude lokasi perusahaan');
            $table->decimal('company_longitude', 11, 8)->nullable()->comment('Longitude lokasi perusahaan');

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('company_code');
            $table->index('phone');
            $table->index(['company_latitude', 'company_longitude']);
            $table->unique('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
