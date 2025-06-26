<?php

namespace App\Class\Helper;

/**
 * Helper class untuk mengelola Delivery Order
 */
class DeliveryOrderHelper
{
    // * ========================================
    // * DELIVERY ORDER STATUS CONSTANTS
    // * ========================================

    const STATUS_DRAFT = 'draft';
    const STATUS_LOADING = 'loading';
    const STATUS_VERIFIED = 'verified';
    const STATUS_DISPATCHED = 'dispatched';
    const STATUS_ARRIVED = 'arrived';
    const STATUS_COMPLETED = 'completed';
    const STATUS_CANCELLED = 'cancelled';

    // * ========================================
    // * STATUS MAPPINGS
    // * ========================================

    /**
     * Mapping warna DaisyUI untuk delivery order status
     */
    private static array $statusColors = [
        self::STATUS_DRAFT => 'neutral',
        self::STATUS_LOADING => 'info',
        self::STATUS_VERIFIED => 'primary',
        self::STATUS_DISPATCHED => 'warning',
        self::STATUS_ARRIVED => 'accent',
        self::STATUS_COMPLETED => 'success',
        self::STATUS_CANCELLED => 'error',
    ];

    /**
     * Mapping label bahasa Indonesia untuk delivery order status
     */
    private static array $statusLabels = [
        self::STATUS_DRAFT => 'Draft',
        self::STATUS_LOADING => 'Loading',
        self::STATUS_VERIFIED => 'Terverifikasi',
        self::STATUS_DISPATCHED => 'Dikirim',
        self::STATUS_ARRIVED => 'Sampai Tujuan',
        self::STATUS_COMPLETED => 'Selesai',
        self::STATUS_CANCELLED => 'Dibatalkan',
    ];

    // * ========================================
    // * HELPER METHODS
    // * ========================================

    /**
     * Get delivery order status color
     */
    public static function getStatusColor(string $status): string
    {
        return self::$statusColors[$status] ?? 'neutral';
    }

    /**
     * Get delivery order status label
     */
    public static function getStatusLabel(string $status): string
    {
        return self::$statusLabels[$status] ?? ucfirst($status);
    }

    /**
     * Get all delivery order statuses untuk dropdown
     */
    public static function getAllStatuses(): array
    {
        return self::$statusLabels;
    }

    /**
     * Get delivery order progress percentage berdasarkan status
     */
    public static function getProgressPercentage(string $status): int
    {
        return match ($status) {
            self::STATUS_DRAFT => 10,
            self::STATUS_LOADING => 25,
            self::STATUS_VERIFIED => 40,
            self::STATUS_DISPATCHED => 60,
            self::STATUS_ARRIVED => 80,
            self::STATUS_COMPLETED => 100,
            self::STATUS_CANCELLED => 0,
            default => 0,
        };
    }

    /**
     * Validate delivery order status
     */
    public static function isValidStatus(string $status): bool
    {
        return array_key_exists($status, self::$statusLabels);
    }
}
