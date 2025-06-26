<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use App\Models\User;
use App\Class\Helper\UserHelper;
use Illuminate\Support\Facades\Hash;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create all roles sesuai dengan konstanta di UserHelper
        $roles = [
            UserHelper::ROLE_ADMIN,
            UserHelper::ROLE_MANAGER,
            UserHelper::ROLE_DRIVER,
            UserHelper::ROLE_CLIENT,
            UserHelper::ROLE_PETUGAS_LAPANGAN,
            UserHelper::ROLE_PETUGAS_RUANGAN,
            UserHelper::ROLE_PETUGAS_GUDANG,
        ];

        foreach ($roles as $roleName) {
            Role::create(['name' => $roleName]);
        }

        // Create demo users untuk testing
        $this->createDemoUsers();
    }

    private function createDemoUsers(): void
    {
        // Demo Admin
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@artelia.dev',
            'password' => Hash::make('@Password123'),
            'email_verified_at' => now(),
            'is_active' => true,
        ]);
        $admin->assignRole(UserHelper::ROLE_ADMIN);

        // Demo Manager
        $manager = User::create([
            'name' => 'Manager User',
            'email' => 'manager@artelia.dev',
            'password' => Hash::make('@Password123'),
            'email_verified_at' => now(),
            'is_active' => true,
        ]);
        $manager->assignRole(UserHelper::ROLE_MANAGER);

        // Demo Driver
        $driver = User::create([
            'name' => 'Driver User',
            'email' => 'driver@artelia.dev',
            'password' => Hash::make('@Password123'),
            'email_verified_at' => now(),
            'is_active' => true,
        ]);
        $driver->assignRole(UserHelper::ROLE_DRIVER);

        // Demo Client
        $client = User::create([
            'name' => 'Client User',
            'email' => 'client@artelia.dev',
            'password' => Hash::make('@Password123'),
            'email_verified_at' => now(),
            'is_active' => true,
        ]);
        $client->assignRole(UserHelper::ROLE_CLIENT);

        // Demo Petugas Lapangan
        $petugasLapangan = User::create([
            'name' => 'Petugas Lapangan',
            'email' => 'lapangan@artelia.dev',
            'password' => Hash::make('@Password123'),
            'email_verified_at' => now(),
            'is_active' => true,
        ]);
        $petugasLapangan->assignRole(UserHelper::ROLE_PETUGAS_LAPANGAN);

        // Demo Petugas Ruangan
        $petugasRuangan = User::create([
            'name' => 'Petugas Ruangan',
            'email' => 'ruangan@artelia.dev',
            'password' => Hash::make('@Password123'),
            'email_verified_at' => now(),
            'is_active' => true,
        ]);
        $petugasRuangan->assignRole(UserHelper::ROLE_PETUGAS_RUANGAN);

        // Demo Petugas Gudang
        $petugasGudang = User::create([
            'name' => 'Petugas Gudang',
            'email' => 'gudang@artelia.dev',
            'password' => Hash::make('@Password123'),
            'email_verified_at' => now(),
            'is_active' => true,
        ]);
        $petugasGudang->assignRole(UserHelper::ROLE_PETUGAS_GUDANG);
    }
}
