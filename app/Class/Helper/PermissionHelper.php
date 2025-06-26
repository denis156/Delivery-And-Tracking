<?php

namespace App\Class\Helper;

/**
 * Helper class untuk mengelola Permission
 */
class PermissionHelper
{
    // * ========================================
    // * PERMISSION CATEGORY CONSTANTS
    // * ========================================

    const CATEGORY_USER = 'user';
    const CATEGORY_ROLE = 'role';
    const CATEGORY_PERMISSION = 'permission';
    const CATEGORY_SYSTEM = 'system';
    const CATEGORY_DELIVERY = 'delivery';
    const CATEGORY_DRIVER = 'driver';
    const CATEGORY_GENERAL = 'general';

    // * ========================================
    // * CATEGORY MAPPINGS
    // * ========================================

    /**
     * Mapping warna DaisyUI untuk permission categories
     */
    private static array $categoryColors = [
        self::CATEGORY_USER => 'primary',
        self::CATEGORY_ROLE => 'secondary',
        self::CATEGORY_PERMISSION => 'accent',
        self::CATEGORY_SYSTEM => 'warning',
        self::CATEGORY_DELIVERY => 'info',
        self::CATEGORY_DRIVER => 'success',
        self::CATEGORY_GENERAL => 'neutral',
    ];

    /**
     * Mapping icon untuk permission categories
     */
    private static array $categoryIcons = [
        self::CATEGORY_USER => 'phosphor.users',
        self::CATEGORY_ROLE => 'phosphor.user-circle',
        self::CATEGORY_PERMISSION => 'phosphor.key',
        self::CATEGORY_SYSTEM => 'phosphor.gear',
        self::CATEGORY_DELIVERY => 'phosphor.truck',
        self::CATEGORY_DRIVER => 'phosphor.car',
        self::CATEGORY_GENERAL => 'phosphor.shield-check',
    ];

    /**
     * Mapping label bahasa Indonesia untuk permission categories
     */
    private static array $categoryLabels = [
        self::CATEGORY_USER => 'Pengguna',
        self::CATEGORY_ROLE => 'Peran',
        self::CATEGORY_PERMISSION => 'Izin',
        self::CATEGORY_SYSTEM => 'Sistem',
        self::CATEGORY_DELIVERY => 'Pengiriman',
        self::CATEGORY_DRIVER => 'Sopir',
        self::CATEGORY_GENERAL => 'Umum',
    ];

    // * ========================================
    // * HELPER METHODS
    // * ========================================

    /**
     * Get permission category dari nama permission
     */
    public static function getPermissionCategory(string $name): string
    {
        $name = strtolower($name);

        if (str_contains($name, 'user')) return self::CATEGORY_USER;
        if (str_contains($name, 'role')) return self::CATEGORY_ROLE;
        if (str_contains($name, 'permission')) return self::CATEGORY_PERMISSION;
        if (str_contains($name, 'system') || str_contains($name, 'admin')) return self::CATEGORY_SYSTEM;
        if (str_contains($name, 'delivery') || str_contains($name, 'order')) return self::CATEGORY_DELIVERY;
        if (str_contains($name, 'driver')) return self::CATEGORY_DRIVER;

        return self::CATEGORY_GENERAL;
    }

    /**
     * Get permission category color
     */
    public static function getCategoryColor(string $category): string
    {
        return self::$categoryColors[$category] ?? 'neutral';
    }

    /**
     * Get permission category icon
     */
    public static function getCategoryIcon(string $category): string
    {
        return self::$categoryIcons[$category] ?? 'phosphor.shield-check';
    }

    /**
     * Get permission category label
     */
    public static function getCategoryLabel(string $category): string
    {
        return self::$categoryLabels[$category] ?? ucfirst($category);
    }

    /**
     * Get all permission categories untuk dropdown
     */
    public static function getAllCategories(): array
    {
        return self::$categoryLabels;
    }

    /**
     * Validate permission category
     */
    public static function isValidCategory(string $category): bool
    {
        return array_key_exists($category, self::$categoryLabels);
    }

    /**
     * Check if permission can be deleted (tidak ada usage)
     */
    public static function canPermissionBeDeleted(\Spatie\Permission\Models\Permission $permission): bool
    {
        return $permission->roles()->count() === 0 && $permission->users()->count() === 0;
    }

    /**
     * Get permission color berdasarkan nama permission
     */
    public static function getPermissionColor(string $name): string
    {
        $category = self::getPermissionCategory($name);
        return self::getCategoryColor($category);
    }

    /**
     * Get permission icon berdasarkan nama permission
     */
    public static function getPermissionIcon(string $name): string
    {
        $category = self::getPermissionCategory($name);
        return self::getCategoryIcon($category);
    }

    /**
     * Get permission category label berdasarkan nama permission
     */
    public static function getPermissionCategoryLabel(string $name): string
    {
        $category = self::getPermissionCategory($name);
        return self::getCategoryLabel($category);
    }

    /**
     * Group permissions by category
     */
    public static function groupPermissionsByCategory(array $permissions): array
    {
        $grouped = [];

        foreach ($permissions as $permission) {
            $category = self::getPermissionCategory($permission);
            $grouped[$category][] = $permission;
        }

        return $grouped;
    }

    /**
     * Get permission usage count (roles + users)
     */
    public static function getPermissionUsageCount(\Spatie\Permission\Models\Permission $permission): int
    {
        return $permission->roles()->count() + $permission->users()->count();
    }

    /**
     * Get permission usage description
     */
    public static function getPermissionUsageDescription(\Spatie\Permission\Models\Permission $permission): string
    {
        $rolesCount = $permission->roles()->count();
        $usersCount = $permission->users()->count();

        if ($rolesCount === 0 && $usersCount === 0) {
            return 'Tidak digunakan';
        }

        $parts = [];
        if ($rolesCount > 0) {
            $parts[] = "{$rolesCount} peran";
        }
        if ($usersCount > 0) {
            $parts[] = "{$usersCount} pengguna";
        }

        return 'Digunakan oleh ' . implode(' dan ', $parts);
    }
}
