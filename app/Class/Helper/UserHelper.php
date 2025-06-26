<?php

namespace App\Class\Helper;

/**
 * Helper class untuk mengelola User
 */
class UserHelper
{
    // * ========================================
    // * USER ROLE CONSTANTS
    // * ========================================

    const ROLE_MANAGER = 'manager';
    const ROLE_ADMIN = 'admin';
    const ROLE_DRIVER = 'driver';
    const ROLE_CLIENT = 'client';
    const ROLE_PETUGAS_LAPANGAN = 'petugas-lapangan';
    const ROLE_PETUGAS_RUANGAN = 'petugas-ruangan';
    const ROLE_PETUGAS_GUDANG = 'petugas-gudang';

    // * ========================================
    // * USER STATUS CONSTANTS
    // * ========================================

    const STATUS_ACTIVE = true;
    const STATUS_INACTIVE = false;

    // * ========================================
    // * ROLE MAPPINGS
    // * ========================================

    /**
     * Mapping warna DaisyUI untuk user roles
     */
    private static array $roleColors = [
        self::ROLE_MANAGER => 'primary',
        self::ROLE_ADMIN => 'info',
        self::ROLE_CLIENT => 'warning',
        self::ROLE_DRIVER => 'error',
        self::ROLE_PETUGAS_LAPANGAN => 'error',
        self::ROLE_PETUGAS_RUANGAN => 'warning',
        self::ROLE_PETUGAS_GUDANG => 'success',
    ];

    /**
     * Mapping label bahasa Indonesia untuk user roles
     */
    private static array $roleLabels = [
        self::ROLE_ADMIN => 'Admin',
        self::ROLE_MANAGER => 'Manajer',
        self::ROLE_CLIENT => 'Klien',
        self::ROLE_DRIVER => 'Sopir',
        self::ROLE_PETUGAS_LAPANGAN => 'Petugas Lapangan',
        self::ROLE_PETUGAS_RUANGAN => 'Petugas Ruangan',
        self::ROLE_PETUGAS_GUDANG => 'Petugas Gudang',
    ];

    /**
     * Mapping icon untuk user roles
     */
    private static array $roleIcons = [
        self::ROLE_ADMIN => 'phosphor.monitor-play',
        self::ROLE_MANAGER => 'phosphor.briefcase',
        self::ROLE_CLIENT => 'phosphor.user',
        self::ROLE_DRIVER => 'phosphor.truck',
        self::ROLE_PETUGAS_LAPANGAN => 'phosphor.hard-hat',
        self::ROLE_PETUGAS_RUANGAN => 'phosphor.desktop-tower',
        self::ROLE_PETUGAS_GUDANG => 'phosphor.garage',
    ];

    // * ========================================
    // * STATUS MAPPINGS
    // * ========================================

    /**
     * Mapping warna DaisyUI untuk user status
     */
    private static array $statusColors = [
        self::STATUS_ACTIVE => 'success',
        self::STATUS_INACTIVE => 'warning',
    ];

    /**
     * Mapping label bahasa Indonesia untuk user status
     */
    private static array $statusLabels = [
        self::STATUS_ACTIVE => 'Aktif',
        self::STATUS_INACTIVE => 'Nonaktif',
    ];

    // * ========================================
    // * ROLE HELPER METHODS
    // * ========================================

    /**
     * Get role color
     */
    public static function getRoleColor(string $role): string
    {
        return self::$roleColors[$role] ?? 'neutral';
    }

    /**
     * Get role label
     */
    public static function getRoleLabel(string $role): string
    {
        return self::$roleLabels[$role] ?? ucfirst($role);
    }

    /**
     * Get role icon
     */
    public static function getRoleIcon(string $role): string
    {
        return self::$roleIcons[$role] ?? 'phosphor.user';
    }

    /**
     * Get all roles untuk dropdown
     */
    public static function getAllRoles(): array
    {
        return self::$roleLabels;
    }

    /**
     * Get all roles excluding drivers
     */
    public static function getRolesExcludeDrivers(): array
    {
        $roles = self::$roleLabels;
        unset($roles[self::ROLE_DRIVER]);
        return $roles;
    }

    /**
     * Get management roles (admin, manager)
     */
    public static function getManagementRoles(): array
    {
        return [
            self::ROLE_ADMIN => self::$roleLabels[self::ROLE_ADMIN],
            self::ROLE_MANAGER => self::$roleLabels[self::ROLE_MANAGER],
        ];
    }

    /**
     * Get staff roles (petugas)
     */
    public static function getStaffRoles(): array
    {
        return [
            self::ROLE_PETUGAS_LAPANGAN => self::$roleLabels[self::ROLE_PETUGAS_LAPANGAN],
            self::ROLE_PETUGAS_RUANGAN => self::$roleLabels[self::ROLE_PETUGAS_RUANGAN],
            self::ROLE_PETUGAS_GUDANG => self::$roleLabels[self::ROLE_PETUGAS_GUDANG],
        ];
    }

    /**
     * Validate role
     */
    public static function isValidRole(string $role): bool
    {
        return array_key_exists($role, self::$roleLabels);
    }

    // * ========================================
    // * STATUS HELPER METHODS
    // * ========================================

    /**
     * Get status color
     */
    public static function getStatusColor(bool $isActive): string
    {
        return self::$statusColors[$isActive];
    }

    /**
     * Get status label
     */
    public static function getStatusLabel(bool $isActive): string
    {
        return self::$statusLabels[$isActive];
    }

    /**
     * Get all statuses untuk dropdown
     */
    public static function getAllStatuses(): array
    {
        return self::$statusLabels;
    }

    // * ========================================
    // * ROLE CHECK METHODS
    // * ========================================

    /**
     * Check if role is management role
     */
    public static function isManagementRole(string $role): bool
    {
        return in_array($role, [self::ROLE_ADMIN, self::ROLE_MANAGER]);
    }

    /**
     * Check if role is staff role
     */
    public static function isStaffRole(string $role): bool
    {
        return in_array($role, [
            self::ROLE_PETUGAS_LAPANGAN,
            self::ROLE_PETUGAS_RUANGAN,
            self::ROLE_PETUGAS_GUDANG
        ]);
    }

    /**
     * Check if role is driver
     */
    public static function isDriverRole(string $role): bool
    {
        return $role === self::ROLE_DRIVER;
    }

    /**
     * Check if role is client
     */
    public static function isClientRole(string $role): bool
    {
        return $role === self::ROLE_CLIENT;
    }

    /**
     * Check if role can access admin features
     */
    public static function canAccessAdmin(string $role): bool
    {
        return in_array($role, [self::ROLE_ADMIN, self::ROLE_MANAGER]);
    }

    /**
     * Check if role can manage users
     */
    public static function canManageUsers(string $role): bool
    {
        return $role === self::ROLE_ADMIN;
    }

    /**
     * Check if role can manage deliveries
     */
    public static function canManageDeliveries(string $role): bool
    {
        return in_array($role, [
            self::ROLE_ADMIN,
            self::ROLE_MANAGER,
            self::ROLE_PETUGAS_LAPANGAN,
            self::ROLE_PETUGAS_RUANGAN,
            self::ROLE_PETUGAS_GUDANG
        ]);
    }

    // * ========================================
    // * UTILITY METHODS
    // * ========================================

    /**
     * Generate avatar placeholder (initials)
     */
    public static function generateAvatarPlaceholder(string $name): string
    {
        $nameParts = explode(' ', $name);
        if (count($nameParts) >= 2) {
            return strtoupper(substr($nameParts[0], 0, 1) . substr($nameParts[1], 0, 1));
        }
        return strtoupper(substr($name, 0, 2));
    }

    /**
     * Format avatar URL with fallback
     */
    public static function formatAvatarUrl(?string $avatarUrl): ?string
    {
        return $avatarUrl ? asset('storage/' . $avatarUrl) : null;
    }

    /**
     * Generate user display name with role
     */
    public static function generateDisplayName(string $name, string $role): string
    {
        $roleLabel = self::getRoleLabel($role);
        return "{$name} ({$roleLabel})";
    }

    /**
     * Get role priority untuk sorting (lower number = higher priority)
     */
    public static function getRolePriority(string $role): int
    {
        return match ($role) {
            self::ROLE_MANAGER => 1,
            self::ROLE_ADMIN => 2,
            self::ROLE_PETUGAS_LAPANGAN => 3,
            self::ROLE_PETUGAS_RUANGAN => 4,
            self::ROLE_PETUGAS_GUDANG => 5,
            self::ROLE_DRIVER => 6,
            self::ROLE_CLIENT => 7,
            default => 99,
        };
    }

    /**
     * Sort users by role priority
     */
    public static function sortByRolePriority(array $users): array
    {
        usort($users, function ($a, $b) {
            $priorityA = self::getRolePriority($a['role'] ?? '');
            $priorityB = self::getRolePriority($b['role'] ?? '');
            return $priorityA <=> $priorityB;
        });

        return $users;
    }

    /**
     * Get role description
     */
    public static function getRoleDescription(string $role): string
    {
        return match ($role) {
            self::ROLE_ADMIN => 'Memiliki akses penuh ke seluruh sistem',
            self::ROLE_MANAGER => 'Mengelola operasional dan monitoring',
            self::ROLE_PETUGAS_LAPANGAN => 'Membuat dan memverifikasi delivery order',
            self::ROLE_PETUGAS_RUANGAN => 'Verifikasi dan approval delivery order',
            self::ROLE_PETUGAS_GUDANG => 'Mengelola loading dan completion',
            self::ROLE_DRIVER => 'Melaksanakan pengiriman barang',
            self::ROLE_CLIENT => 'Menerima dan konfirmasi barang',
            default => 'Role tidak dikenal',
        };
    }

    /**
     * Get allowed transitions untuk role changes
     */
    public static function getAllowedRoleTransitions(string $currentRole): array
    {
        // Admin can change to any role except client
        if ($currentRole === self::ROLE_ADMIN) {
            $roles = self::getAllRoles();
            unset($roles[self::ROLE_CLIENT]);
            return $roles;
        }

        // Manager can change to staff roles only
        if ($currentRole === self::ROLE_MANAGER) {
            return self::getStaffRoles() + [self::ROLE_DRIVER => self::$roleLabels[self::ROLE_DRIVER]];
        }

        // Staff can only change between staff roles
        if (self::isStaffRole($currentRole)) {
            return self::getStaffRoles();
        }

        // Driver and Client cannot change roles
        return [];
    }
}
