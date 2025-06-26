<?php

namespace App\Class\Helper;

/**
 * Helper class untuk mengelola Item
 */
class ItemHelper
{
    // * ========================================
    // * ITEM STATUS CONSTANTS
    // * ========================================

    const STATUS_PREPARED = 'prepared';
    const STATUS_LOADED = 'loaded';
    const STATUS_DELIVERED = 'delivered';
    const STATUS_DAMAGED = 'damaged';
    const STATUS_RETURNED = 'returned';

    // * ========================================
    // * ITEM CONDITION CONSTANTS
    // * ========================================

    const CONDITION_BAIK = 'baik';
    const CONDITION_RUSAK_RINGAN = 'rusak_ringan';
    const CONDITION_RUSAK_BERAT = 'rusak_berat';

    // * ========================================
    // * STATUS MAPPINGS
    // * ========================================

    /**
     * Mapping warna DaisyUI untuk item status
     */
    private static array $statusColors = [
        self::STATUS_PREPARED => 'neutral',
        self::STATUS_LOADED => 'info',
        self::STATUS_DELIVERED => 'success',
        self::STATUS_DAMAGED => 'error',
        self::STATUS_RETURNED => 'warning',
    ];

    /**
     * Mapping label bahasa Indonesia untuk item status
     */
    private static array $statusLabels = [
        self::STATUS_PREPARED => 'Disiapkan',
        self::STATUS_LOADED => 'Dimuat',
        self::STATUS_DELIVERED => 'Terkirim',
        self::STATUS_DAMAGED => 'Rusak',
        self::STATUS_RETURNED => 'Dikembalikan',
    ];

    // * ========================================
    // * CONDITION MAPPINGS
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
    // * STATUS HELPER METHODS
    // * ========================================

    /**
     * Get item status color
     */
    public static function getStatusColor(string $status): string
    {
        return self::$statusColors[$status] ?? 'neutral';
    }

    /**
     * Get item status label
     */
    public static function getStatusLabel(string $status): string
    {
        return self::$statusLabels[$status] ?? ucfirst($status);
    }

    /**
     * Get all item statuses untuk dropdown
     */
    public static function getAllStatuses(): array
    {
        return self::$statusLabels;
    }

    /**
     * Validate item status
     */
    public static function isValidStatus(string $status): bool
    {
        return array_key_exists($status, self::$statusLabels);
    }

    // * ========================================
    // * CONDITION HELPER METHODS
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

    /**
     * Validate condition
     */
    public static function isValidCondition(string $condition): bool
    {
        return array_key_exists($condition, self::$conditionLabels);
    }
}
