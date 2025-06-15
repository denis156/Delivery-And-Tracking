<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
            'role' => fake()->randomElement([
                User::ROLE_ADMIN,
                User::ROLE_MANAGER,
                User::ROLE_CLIENT,
                User::ROLE_DRIVER,
                User::ROLE_PETUGAS_LAPANGAN,
                User::ROLE_PETUGAS_RUANGAN,
                User::ROLE_PETUGAS_GUDANG,
            ]),
            'is_active' => fake()->boolean(85), // 85% chance active
            'avatar_url' => null,
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    /**
     * Create admin user
     */
    public function admin(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => User::ROLE_ADMIN,
            'is_active' => true,
            'email_verified_at' => now(),
        ]);
    }

    /**
     * Create manager user
     */
    public function manager(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => User::ROLE_MANAGER,
            'is_active' => true,
            'email_verified_at' => now(),
        ]);
    }

    /**
     * Create driver user
     */
    public function driver(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => User::ROLE_DRIVER,
            'is_active' => true,
            'email_verified_at' => now(),
            'name' => fake()->randomElement([
                'Ahmad Supardi',
                'Budi Santoso',
                'Candra Wijaya',
                'Dedi Kurniawan',
                'Eko Prasetyo',
                'Fajar Nugroho',
                'Gunawan Saputra',
                'Hendra Kusuma',
                'Indra Firmansyah',
                'Joko Widodo (Driver)',
            ]),
        ]);
    }

    /**
     * Create client user
     */
    public function client(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => User::ROLE_CLIENT,
            'is_active' => true,
            'email_verified_at' => now(),
            'name' => fake()->randomElement([
                'PT. Maju Bersama',
                'CV. Sukses Mandiri',
                'UD. Berkah Jaya',
                'PT. Teknologi Nusantara',
                'CV. Karya Utama',
                'PT. Global Solutions',
                'UD. Sinar Harapan',
                'PT. Indo Makmur',
                'CV. Bintang Terang',
                'PT. Harmoni Sejahtera',
            ]),
        ]);
    }

    /**
     * Create petugas lapangan user
     */
    public function petugasLapangan(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => User::ROLE_PETUGAS_LAPANGAN,
            'is_active' => true,
            'email_verified_at' => now(),
            'name' => fake()->randomElement([
                'Arif Budiman (PL)',
                'Bambang Sutrisno (PL)',
                'Catur Wibowo (PL)',
                'Dwi Handoko (PL)',
                'Edi Nurhayat (PL)',
            ]),
        ]);
    }

    /**
     * Create petugas ruangan user
     */
    public function petugasRuangan(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => User::ROLE_PETUGAS_RUANGAN,
            'is_active' => true,
            'email_verified_at' => now(),
            'name' => fake()->randomElement([
                'Ani Suryani (PR)',
                'Budi Hartono (PR)',
                'Cici Amelia (PR)',
                'Diah Permata (PR)',
                'Erni Wahyuni (PR)',
            ]),
        ]);
    }

    /**
     * Create petugas gudang user
     */
    public function petugasGudang(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => User::ROLE_PETUGAS_GUDANG,
            'is_active' => true,
            'email_verified_at' => now(),
            'name' => fake()->randomElement([
                'Agus Setiawan (PG)',
                'Beni Gunawan (PG)',
                'Cahyo Utomo (PG)',
                'Dodi Purnama (PG)',
                'Eko Supriyadi (PG)',
            ]),
        ]);
    }

    /**
     * Create inactive user
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Create user with specific email for testing
     */
    public function withEmail(string $email): static
    {
        return $this->state(fn (array $attributes) => [
            'email' => $email,
        ]);
    }
}
