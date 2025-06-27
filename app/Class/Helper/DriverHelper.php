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
    // * CONFIGURATION CONSTANTS
    // * ========================================

    const LICENSE_NUMBER_MIN_LENGTH = 10;
    const LICENSE_WARNING_DAYS = 90;
    const DEFAULT_EMPTY_VALUE = '-';
    const DEFAULT_ICON = 'phosphor.question';
    const DEFAULT_COLOR = 'neutral';

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

    /**
     * Mapping label untuk license status
     */
    private static array $licenseStatusLabels = [
        'expired' => 'SIM Kadaluarsa',
        'expiring_soon' => 'SIM Akan Kadaluarsa',
        'valid' => 'SIM Berlaku',
        'no_license' => 'Belum Ada SIM',
        'invalid' => 'SIM Tidak Valid',
    ];

    /**
     * Mapping label untuk vehicle status
     */
    private static array $vehicleStatusLabels = [
        'complete' => 'Kendaraan Lengkap',
        'partial' => 'Kendaraan Sebagian',
        'none' => 'Belum Ada Kendaraan',
    ];

    // * ========================================
    // * UI MAPPINGS - NEW SECTION
    // * ========================================

    /**
     * Mapping icon untuk driver fields
     */
    private static array $driverFieldIcons = [
        'license_type' => 'phosphor.identification-badge',
        'license_number' => 'phosphor.identification-card',
        'license_expiry' => 'phosphor.calendar-x',
        'license_status' => 'phosphor.shield-check',
        'phone' => 'phosphor.phone',
        'address' => 'phosphor.house',
        'vehicle_type' => 'phosphor.truck-trailer',
        'vehicle_plate' => 'phosphor.hash',
        'vehicle_status' => 'phosphor.truck',
        'driver_display' => 'phosphor.identification-card',
    ];

    /**
     * Mapping warna untuk driver fields
     */
    private static array $driverFieldColors = [
        'license_type' => 'dynamic', // Akan menggunakan license color
        'license_number' => 'info',
        'license_expiry' => 'error',
        'license_status' => 'dynamic', // Akan menggunakan status color
        'phone' => 'secondary',
        'address' => 'accent',
        'vehicle_type' => 'primary',
        'vehicle_plate' => 'secondary',
        'vehicle_status' => 'info',
        'driver_display' => 'accent',
    ];

    /**
     * Mapping icon untuk license status
     */
    private static array $licenseStatusIcons = [
        'expired' => 'phosphor.x-circle',
        'expiring_soon' => 'phosphor.warning',
        'valid' => 'phosphor.check-circle',
        'no_license' => 'phosphor.warning-octagon',
    ];

    /**
     * Mapping icon untuk vehicle status
     */
    private static array $vehicleStatusIcons = [
        'complete' => 'phosphor.check-circle',
        'partial' => 'phosphor.warning',
        'none' => 'phosphor.x-circle',
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
        return self::$licenseColors[$licenseType] ?? self::DEFAULT_COLOR;
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
     * CORRECTED: Check if license is expired
     */
    public static function isLicenseExpired(\DateTime|\Carbon\Carbon|string $expiryDate): bool
    {
        if (is_string($expiryDate)) {
            $expiryDate = \Carbon\Carbon::parse($expiryDate);
        } elseif ($expiryDate instanceof \DateTime) {
            $expiryDate = \Carbon\Carbon::instance($expiryDate);
        }

        // Simple check: apakah tanggal kadaluarsa sudah lewat hari ini?
        return $expiryDate->endOfDay()->isPast();
    }

    /**
     * CORRECTED: Check if license expires soon (dalam 90 hari)
     */
    public static function isLicenseExpiringSoon(\DateTime|\Carbon\Carbon|string $expiryDate, int $warningDays = 90): bool
    {
        if (is_string($expiryDate)) {
            $expiryDate = \Carbon\Carbon::parse($expiryDate);
        } elseif ($expiryDate instanceof \DateTime) {
            $expiryDate = \Carbon\Carbon::instance($expiryDate);
        }

        // Logic sederhana:
        // 1. SIM belum expired
        // 2. Dan tanggal kadaluarsa dalam rentang warning days dari sekarang
        return !self::isLicenseExpired($expiryDate) &&
               $expiryDate->diffInDays(now(), true) <= $warningDays;
    }

    // * ========================================
    // * NEW: UI HELPER METHODS
    // * ========================================

    /**
     * Get icon untuk driver field
     */
    public static function getDriverFieldIcon(string $field): string
    {
        return self::$driverFieldIcons[$field] ?? self::DEFAULT_ICON;
    }

    /**
     * Get color untuk driver field
     */
    public static function getDriverFieldColor(string $field): string
    {
        return self::$driverFieldColors[$field] ?? self::DEFAULT_COLOR;
    }

    /**
     * Get icon untuk license status
     */
    public static function getLicenseStatusIcon(string $status): string
    {
        return self::$licenseStatusIcons[$status] ?? self::DEFAULT_ICON;
    }

    /**
     * Get label untuk license status
     */
    public static function getLicenseStatusLabel(string $status): string
    {
        return self::$licenseStatusLabels[$status] ?? ucfirst($status);
    }

    /**
     * Get icon untuk vehicle status
     */
    public static function getVehicleStatusIcon(string $status): string
    {
        return self::$vehicleStatusIcons[$status] ?? self::DEFAULT_ICON;
    }

    /**
     * Get label untuk vehicle status
     */
    public static function getVehicleStatusLabel(string $status): string
    {
        return self::$vehicleStatusLabels[$status] ?? ucfirst($status);
    }

    /**
     * Get vehicle status dengan icon dan label
     */
    public static function getVehicleStatus(string $status): array
    {
        return [
            'status' => $status,
            'label' => self::getVehicleStatusLabel($status),
            'icon' => self::getVehicleStatusIcon($status),
            'color' => match($status) {
                'complete' => 'success',
                'partial' => 'warning', 
                'none' => 'error',
                default => self::DEFAULT_COLOR
            }
        ];
    }

    /**
     * Get icon dengan logic kondisional untuk license
     */
    public static function getLicenseStatusIconDynamic(bool $isExpired, bool $isExpiringSoon): string
    {
        if ($isExpired) {
            return self::$licenseStatusIcons['expired'];
        } elseif ($isExpiringSoon) {
            return self::$licenseStatusIcons['expiring_soon'];
        } else {
            return self::$licenseStatusIcons['valid'];
        }
    }

    /**
     * Get all driver field icons untuk reference
     */
    public static function getAllDriverFieldIcons(): array
    {
        return self::$driverFieldIcons;
    }

    /**
     * Get all driver field colors untuk reference
     */
    public static function getAllDriverFieldColors(): array
    {
        return self::$driverFieldColors;
    }

    /**
     * Get all license status labels untuk reference
     */
    public static function getAllLicenseStatusLabels(): array
    {
        return self::$licenseStatusLabels;
    }

    /**
     * Get all vehicle status labels untuk reference
     */
    public static function getAllVehicleStatusLabels(): array
    {
        return self::$vehicleStatusLabels;
    }

    /**
     * Get all license status icons untuk reference
     */
    public static function getAllLicenseStatusIcons(): array
    {
        return self::$licenseStatusIcons;
    }

    /**
     * Get all vehicle status icons untuk reference
     */
    public static function getAllVehicleStatusIcons(): array
    {
        return self::$vehicleStatusIcons;
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

        if (strlen($clean) >= self::LICENSE_NUMBER_MIN_LENGTH) {
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
            return self::DEFAULT_EMPTY_VALUE;
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
     * CORRECTED: Get license status dengan logic yang sederhana
     */
    public static function getLicenseStatus(\DateTime|\Carbon\Carbon|string $expiryDate): array
    {
        if (is_string($expiryDate)) {
            $expiryDate = \Carbon\Carbon::parse($expiryDate);
        } elseif ($expiryDate instanceof \DateTime) {
            $expiryDate = \Carbon\Carbon::instance($expiryDate);
        }

        // Cek expired dulu
        if (self::isLicenseExpired($expiryDate)) {
            return [
                'status' => 'expired',
                'label' => self::getLicenseStatusLabel('expired'),
                'color' => 'error',
                'icon' => self::getLicenseStatusIcon('expired'),
            ];
        }

        // Cek expiring soon (dengan konfigurasi warning days)
        if (self::isLicenseExpiringSoon($expiryDate, self::LICENSE_WARNING_DAYS)) {
            return [
                'status' => 'expiring_soon',
                'label' => self::getLicenseStatusLabel('expiring_soon'),
                'color' => 'warning',
                'icon' => self::getLicenseStatusIcon('expiring_soon'),
            ];
        }

        // SIM masih valid dan aman
        return [
            'status' => 'valid',
            'label' => self::getLicenseStatusLabel('valid'),
            'color' => 'success',
            'icon' => self::getLicenseStatusIcon('valid'),
        ];
    }

    /**
     * CORRECTED: Get days to expiry dengan logic yang benar
     */
    public static function getDaysToExpiry(\DateTime|\Carbon\Carbon|string $expiryDate): int
    {
        if (is_string($expiryDate)) {
            $expiryDate = \Carbon\Carbon::parse($expiryDate);
        } elseif ($expiryDate instanceof \DateTime) {
            $expiryDate = \Carbon\Carbon::instance($expiryDate);
        }

        // Gunakan diffInDays dengan absolute true untuk selalu dapat nilai positif
        return (int) $expiryDate->diffInDays(now(), true);
    }
}
