<?php

namespace App\Class\Helper;

/**
 * Helper class untuk mengelola Driver
 */
class DriverHelper
{
    // * ========================================
    // * LICENSE TYPE CONSTANTS (dari migration)
    // * ========================================

    const LICENSE_A = 'A';
    const LICENSE_B1 = 'B1';
    const LICENSE_B2 = 'B2';
    const LICENSE_D = 'D';
    const LICENSE_A_UMUM = 'A UMUM';
    const LICENSE_B1_UMUM = 'B1 UMUM';
    const LICENSE_B2_UMUM = 'B2 UMUM';

    // * ========================================
    // * LICENSE MAPPINGS
    // * ========================================

    /**
     * Mapping label untuk license types
     */
    private static array $licenseLabels = [
        self::LICENSE_A => 'SIM A',
        self::LICENSE_B1 => 'SIM B1',
        self::LICENSE_B2 => 'SIM B2',
        self::LICENSE_D => 'SIM D',
        self::LICENSE_A_UMUM => 'SIM A Umum',
        self::LICENSE_B1_UMUM => 'SIM B1 Umum',
        self::LICENSE_B2_UMUM => 'SIM B2 Umum',
    ];

    /**
     * Mapping warna untuk license types
     */
    private static array $licenseColors = [
        self::LICENSE_A => 'info',
        self::LICENSE_B1 => 'primary',
        self::LICENSE_B2 => 'accent',
        self::LICENSE_D => 'secondary',
        self::LICENSE_A_UMUM => 'info',
        self::LICENSE_B1_UMUM => 'primary',
        self::LICENSE_B2_UMUM => 'accent',
    ];

    // * ========================================
    // * LICENSE HELPER METHODS
    // * ========================================

    /**
     * Get license type label
     */
    public static function getLicenseLabel(string $licenseType): string
    {
        return self::$licenseLabels[$licenseType] ?? $licenseType;
    }

    /**
     * Get license type color
     */
    public static function getLicenseColor(string $licenseType): string
    {
        return self::$licenseColors[$licenseType] ?? 'neutral';
    }

    /**
     * Get all license types untuk dropdown
     */
    public static function getAllLicenseTypes(): array
    {
        return self::$licenseLabels;
    }

    /**
     * Validate license type
     */
    public static function isValidLicenseType(string $licenseType): bool
    {
        return array_key_exists($licenseType, self::$licenseLabels);
    }

    /**
     * Check if license is expired
     */
    public static function isLicenseExpired(\DateTime|\Carbon\Carbon|string $expiryDate): bool
    {
        if (is_string($expiryDate)) {
            $expiryDate = \Carbon\Carbon::parse($expiryDate);
        } elseif ($expiryDate instanceof \DateTime) {
            $expiryDate = \Carbon\Carbon::instance($expiryDate);
        }

        return $expiryDate->isPast();
    }

    /**
     * Check if license expires soon (dalam 30 hari)
     */
    public static function isLicenseExpiringSoon(\DateTime|\Carbon\Carbon|string $expiryDate): bool
    {
        if (is_string($expiryDate)) {
            $expiryDate = \Carbon\Carbon::parse($expiryDate);
        } elseif ($expiryDate instanceof \DateTime) {
            $expiryDate = \Carbon\Carbon::instance($expiryDate);
        }

        return $expiryDate->diffInDays(now()) <= 30 && $expiryDate->isFuture();
    }

    // * ========================================
    // * UTILITY METHODS
    // * ========================================

    /**
     * Format license number untuk display
     */
    public static function formatLicenseNumber(string $licenseNumber): string
    {
        // Format: 1234 5678 90 (untuk SIM)
        $clean = preg_replace('/[^0-9]/', '', $licenseNumber);

        if (strlen($clean) >= 10) {
            return substr($clean, 0, 4) . ' ' .
                   substr($clean, 4, 4) . ' ' .
                   substr($clean, 8);
        }

        return $licenseNumber;
    }

    /**
     * Format vehicle plate untuk display
     */
    public static function formatVehiclePlate(?string $plate): string
    {
        if (empty($plate)) {
            return '-';
        }

        return strtoupper($plate);
    }

    /**
     * Get driver display name with vehicle info
     */
    public static function formatDriverDisplayName(string $name, ?string $vehicleType = null, ?string $vehiclePlate = null): string
    {
        $display = $name;

        if ($vehicleType) {
            $display .= " ({$vehicleType}";

            if ($vehiclePlate) {
                $display .= " - {$vehiclePlate}";
            }

            $display .= ")";
        }

        return $display;
    }

    /**
     * Get license status (valid, expired, expiring soon)
     */
    public static function getLicenseStatus(\DateTime|\Carbon\Carbon|string $expiryDate): array
    {
        if (self::isLicenseExpired($expiryDate)) {
            return [
                'status' => 'expired',
                'label' => 'Kadaluarsa',
                'color' => 'error'
            ];
        }

        if (self::isLicenseExpiringSoon($expiryDate)) {
            return [
                'status' => 'expiring_soon',
                'label' => 'Akan Kadaluarsa',
                'color' => 'warning'
            ];
        }

        return [
            'status' => 'valid',
            'label' => 'Berlaku',
            'color' => 'success'
        ];
    }
}
