<?php

namespace App\Class;

/**
 * Helper class untuk mengelola status, color, dan label
 * Menghindari duplikasi kode di model DeliveryOrder, Item dan Permission & Role
 */
class StatusHelper
{
    // * ========================================
    // * DELIVERY ORDER STATUS CONSTANTS
    // * ========================================

    const DO_STATUS_DRAFT = 'draft';
    const DO_STATUS_LOADING = 'loading';
    const DO_STATUS_VERIFIED = 'verified';
    const DO_STATUS_DISPATCHED = 'dispatched';
    const DO_STATUS_ARRIVED = 'arrived';
    const DO_STATUS_COMPLETED = 'completed';
    const DO_STATUS_CANCELLED = 'cancelled';

    // * ========================================
    // * ITEM STATUS CONSTANTS
    // * ========================================

    const ITEM_STATUS_PREPARED = 'prepared';
    const ITEM_STATUS_LOADED = 'loaded';
    const ITEM_STATUS_DELIVERED = 'delivered';
    const ITEM_STATUS_DAMAGED = 'damaged';
    const ITEM_STATUS_RETURNED = 'returned';

    // * ========================================
    // * ITEM CONDITION CONSTANTS
    // * ========================================

    const CONDITION_BAIK = 'baik';
    const CONDITION_RUSAK_RINGAN = 'rusak_ringan';
    const CONDITION_RUSAK_BERAT = 'rusak_berat';

    // * ========================================
    // * PERMISSION CATEGORY CONSTANTS
    // * ========================================

    const PERMISSION_CATEGORY_USER = 'user';
    const PERMISSION_CATEGORY_ROLE = 'role';
    const PERMISSION_CATEGORY_PERMISSION = 'permission';
    const PERMISSION_CATEGORY_SYSTEM = 'system';
    const PERMISSION_CATEGORY_DELIVERY = 'delivery';
    const PERMISSION_CATEGORY_DRIVER = 'driver';
    const PERMISSION_CATEGORY_GENERAL = 'general';

    // * ========================================
    // * DELIVERY ORDER STATUS MAPPINGS
    // * ========================================

    /**
     * Mapping warna DaisyUI untuk delivery order status
     */
    private static array $deliveryOrderStatusColors = [
        self::DO_STATUS_DRAFT => 'neutral',
        self::DO_STATUS_LOADING => 'info',
        self::DO_STATUS_VERIFIED => 'primary',
        self::DO_STATUS_DISPATCHED => 'warning',
        self::DO_STATUS_ARRIVED => 'accent',
        self::DO_STATUS_COMPLETED => 'success',
        self::DO_STATUS_CANCELLED => 'error',
    ];

    /**
     * Mapping label bahasa Indonesia untuk delivery order status
     */
    private static array $deliveryOrderStatusLabels = [
        self::DO_STATUS_DRAFT => 'Draft',
        self::DO_STATUS_LOADING => 'Loading',
        self::DO_STATUS_VERIFIED => 'Terverifikasi',
        self::DO_STATUS_DISPATCHED => 'Dikirim',
        self::DO_STATUS_ARRIVED => 'Sampai Tujuan',
        self::DO_STATUS_COMPLETED => 'Selesai',
        self::DO_STATUS_CANCELLED => 'Dibatalkan',
    ];

    // * ========================================
    // * ITEM STATUS MAPPINGS
    // * ========================================

    /**
     * Mapping warna DaisyUI untuk item status
     */
    private static array $itemStatusColors = [
        self::ITEM_STATUS_PREPARED => 'neutral',
        self::ITEM_STATUS_LOADED => 'info',
        self::ITEM_STATUS_DELIVERED => 'success',
        self::ITEM_STATUS_DAMAGED => 'error',
        self::ITEM_STATUS_RETURNED => 'warning',
    ];

    /**
     * Mapping label bahasa Indonesia untuk item status
     */
    private static array $itemStatusLabels = [
        self::ITEM_STATUS_PREPARED => 'Disiapkan',
        self::ITEM_STATUS_LOADED => 'Dimuat',
        self::ITEM_STATUS_DELIVERED => 'Terkirim',
        self::ITEM_STATUS_DAMAGED => 'Rusak',
        self::ITEM_STATUS_RETURNED => 'Dikembalikan',
    ];

    // * ========================================
    // * ITEM CONDITION MAPPINGS
    // * ========================================

    /**
     * Mapping warna DaisyUI untuk kondisi item
     */
    private static array $conditionColors = [
        self::CONDITION_BAIK => 'success',
        self::CONDITION_RUSAK_RINGAN => 'warning',
        self::CONDITION_RUSAK_BERAT => 'error',
    ];

    /**
     * Mapping label bahasa Indonesia untuk kondisi item
     */
    private static array $conditionLabels = [
        self::CONDITION_BAIK => 'Baik',
        self::CONDITION_RUSAK_RINGAN => 'Rusak Ringan',
        self::CONDITION_RUSAK_BERAT => 'Rusak Berat',
    ];

    // * ========================================
    // * PERMISSION CATEGORY MAPPINGS
    // * ========================================

    /**
     * Mapping warna DaisyUI untuk permission categories
     */
    private static array $permissionCategoryColors = [
        self::PERMISSION_CATEGORY_USER => 'primary',
        self::PERMISSION_CATEGORY_ROLE => 'secondary',
        self::PERMISSION_CATEGORY_PERMISSION => 'accent',
        self::PERMISSION_CATEGORY_SYSTEM => 'warning',
        self::PERMISSION_CATEGORY_DELIVERY => 'info',
        self::PERMISSION_CATEGORY_DRIVER => 'success',
        self::PERMISSION_CATEGORY_GENERAL => 'neutral',
    ];

    /**
     * Mapping icon untuk permission categories
     */
    private static array $permissionCategoryIcons = [
        self::PERMISSION_CATEGORY_USER => 'phosphor.users',
        self::PERMISSION_CATEGORY_ROLE => 'phosphor.user-circle',
        self::PERMISSION_CATEGORY_PERMISSION => 'phosphor.key',
        self::PERMISSION_CATEGORY_SYSTEM => 'phosphor.gear',
        self::PERMISSION_CATEGORY_DELIVERY => 'phosphor.truck',
        self::PERMISSION_CATEGORY_DRIVER => 'phosphor.car',
        self::PERMISSION_CATEGORY_GENERAL => 'phosphor.shield-check',
    ];

    // * ========================================
    // * DELIVERY ORDER HELPER METHODS
    // * ========================================

    /**
     * Get delivery order status color
     */
    public static function getDeliveryOrderStatusColor(string $status): string
    {
        return self::$deliveryOrderStatusColors[$status] ?? 'neutral';
    }

    /**
     * Get delivery order status label
     */
    public static function getDeliveryOrderStatusLabel(string $status): string
    {
        return self::$deliveryOrderStatusLabels[$status] ?? ucfirst($status);
    }

    /**
     * Get all delivery order statuses untuk dropdown
     */
    public static function getAllDeliveryOrderStatuses(): array
    {
        return self::$deliveryOrderStatusLabels;
    }

    /**
     * Get delivery order progress percentage berdasarkan status
     */
    public static function getDeliveryOrderProgressPercentage(string $status): int
    {
        return match ($status) {
            self::DO_STATUS_DRAFT => 10,
            self::DO_STATUS_LOADING => 25,
            self::DO_STATUS_VERIFIED => 40,
            self::DO_STATUS_DISPATCHED => 60,
            self::DO_STATUS_ARRIVED => 80,
            self::DO_STATUS_COMPLETED => 100,
            self::DO_STATUS_CANCELLED => 0,
            default => 0,
        };
    }

    // * ========================================
    // * ITEM STATUS HELPER METHODS
    // * ========================================

    /**
     * Get item status color
     */
    public static function getItemStatusColor(string $status): string
    {
        return self::$itemStatusColors[$status] ?? 'neutral';
    }

    /**
     * Get item status label
     */
    public static function getItemStatusLabel(string $status): string
    {
        return self::$itemStatusLabels[$status] ?? ucfirst($status);
    }

    /**
     * Get all item statuses untuk dropdown
     */
    public static function getAllItemStatuses(): array
    {
        return self::$itemStatusLabels;
    }

    // * ========================================
    // * ITEM CONDITION HELPER METHODS
    // * ========================================

    /**
     * Get condition color
     */
    public static function getConditionColor(string $condition): string
    {
        return self::$conditionColors[$condition] ?? 'neutral';
    }

    /**
     * Get condition label
     */
    public static function getConditionLabel(string $condition): string
    {
        return self::$conditionLabels[$condition] ?? ucfirst($condition);
    }

    /**
     * Get all conditions untuk dropdown
     */
    public static function getAllConditions(): array
    {
        return self::$conditionLabels;
    }

    // * ========================================
    // * PERMISSION HELPER METHODS
    // * ========================================

    /**
     * Get permission category dari nama permission
     */
    public static function getPermissionCategory(string $name): string
    {
        $name = strtolower($name);

        if (str_contains($name, 'user')) return self::PERMISSION_CATEGORY_USER;
        if (str_contains($name, 'role')) return self::PERMISSION_CATEGORY_ROLE;
        if (str_contains($name, 'permission')) return self::PERMISSION_CATEGORY_PERMISSION;
        if (str_contains($name, 'system') || str_contains($name, 'admin')) return self::PERMISSION_CATEGORY_SYSTEM;
        if (str_contains($name, 'delivery') || str_contains($name, 'order')) return self::PERMISSION_CATEGORY_DELIVERY;
        if (str_contains($name, 'driver')) return self::PERMISSION_CATEGORY_DRIVER;

        return self::PERMISSION_CATEGORY_GENERAL;
    }

    /**
     * Get permission category color
     */
    public static function getPermissionColor(string $category): string
    {
        return self::$permissionCategoryColors[$category] ?? 'neutral';
    }

    /**
     * Get permission category icon
     */
    public static function getPermissionIcon(string $category): string
    {
        return self::$permissionCategoryIcons[$category] ?? 'phosphor.shield-check';
    }

    /**
     * Check if permission can be deleted (tidak ada usage)
     */
    public static function canPermissionBeDeleted(\Spatie\Permission\Models\Permission $permission): bool
    {
        return $permission->roles()->count() === 0 && $permission->users()->count() === 0;
    }

    // * ========================================
    // * FORMATTING HELPER METHODS
    // * ========================================

    /**
     * Format rupiah currency
     */
    public static function formatRupiah(float $amount): string
    {
        return 'Rp ' . number_format($amount, 0, ',', '.');
    }

    /**
     * Format weight dengan satuan kilogram
     */
    public static function formatWeight(float $weight): string
    {
        return number_format($weight, 1) . ' kg';
    }

    /**
     * Format quantity dengan satuan
     */
    public static function formatQuantity(float $quantity, string $unit): string
    {
        return number_format($quantity, 0) . ' ' . $unit;
    }

    /**
     * Generate barcode dengan prefix dan format tanggal
     */
    public static function generateBarcode(string $prefix, int $randomLength = 6): string
    {
        $date = now()->format('ymd');
        $random = strtoupper(\Illuminate\Support\Str::random($randomLength));

        return "{$prefix}{$date}{$random}";
    }

    /**
     * Generate order number dengan prefix dan format tanggal
     */
    public static function generateOrderNumber(string $prefix, int $randomLength = 4): string
    {
        $date = now()->format('ymd');
        $random = strtoupper(\Illuminate\Support\Str::random($randomLength));

        return "{$prefix}{$date}{$random}";
    }

    // * ========================================
    // * VALIDATION HELPER METHODS
    // * ========================================

    /**
     * Validate delivery order status
     */
    public static function isValidDeliveryOrderStatus(string $status): bool
    {
        return array_key_exists($status, self::$deliveryOrderStatusLabels);
    }

    /**
     * Validate item status
     */
    public static function isValidItemStatus(string $status): bool
    {
        return array_key_exists($status, self::$itemStatusLabels);
    }

    /**
     * Validate condition
     */
    public static function isValidCondition(string $condition): bool
    {
        return array_key_exists($condition, self::$conditionLabels);
    }

    /**
     * Validate permission category
     */
    public static function isValidPermissionCategory(string $category): bool
    {
        return array_key_exists($category, self::$permissionCategoryColors);
    }
}
