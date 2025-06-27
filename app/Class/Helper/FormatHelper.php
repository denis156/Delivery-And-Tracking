<?php

namespace App\Class\Helper;

/**
 * Helper class untuk formatting data
 */
class FormatHelper
{
    // * ========================================
    // * CURRENCY FORMATTING
    // * ========================================

    /**
     * Format rupiah currency
     */
    public static function formatRupiah(float $amount): string
    {
        return 'Rp ' . number_format($amount, 0, ',', '.');
    }

    /**
     * Format rupiah with decimal
     */
    public static function formatRupiahWithDecimal(float $amount, int $decimals = 2): string
    {
        return 'Rp ' . number_format($amount, $decimals, ',', '.');
    }

    // * ========================================
    // * WEIGHT FORMATTING
    // * ========================================

    /**
     * Format weight dengan satuan kilogram
     */
    public static function formatWeight(float $weight): string
    {
        return number_format($weight, 1) . ' kg';
    }

    /**
     * Format weight dengan precision custom
     */
    public static function formatWeightWithPrecision(float $weight, int $precision = 1): string
    {
        return number_format($weight, $precision) . ' kg';
    }

    // * ========================================
    // * QUANTITY FORMATTING
    // * ========================================

    /**
     * Format quantity dengan satuan
     */
    public static function formatQuantity(float $quantity, string $unit): string
    {
        return number_format($quantity, 0) . ' ' . $unit;
    }

    /**
     * Format quantity dengan decimal
     */
    public static function formatQuantityWithDecimal(float $quantity, string $unit, int $decimals = 2): string
    {
        return number_format($quantity, $decimals) . ' ' . $unit;
    }

    // * ========================================
    // * NUMBER FORMATTING
    // * ========================================

    /**
     * Format number dengan thousand separator
     */
    public static function formatNumber(float $number, int $decimals = 0): string
    {
        return number_format($number, $decimals, ',', '.');
    }

    /**
     * Format percentage
     */
    public static function formatPercentage(float $percentage, int $decimals = 1): string
    {
        return number_format($percentage, $decimals) . '%';
    }

    // * ========================================
    // * DATE & TIME FORMATTING
    // * ========================================

    /**
     * Format date untuk Indonesia
     */
    public static function formatDate(\DateTime|\Carbon\Carbon|string $date): string
    {
        if (is_string($date)) {
            $date = \Carbon\Carbon::parse($date);
        }

        return $date->format('d/m/Y');
    }

    /**
     * Format datetime untuk Indonesia
     */
    public static function formatDateTime(\DateTime|\Carbon\Carbon|string $datetime): string
    {
        if (is_string($datetime)) {
            $datetime = \Carbon\Carbon::parse($datetime);
        }

        return $datetime->format('d/m/Y H:i');
    }

    /**
     * Format date untuk display dengan nama hari dan bulan
     */
    public static function formatDateForDisplay(\DateTime|\Carbon\Carbon|string $date): string
    {
        if (is_string($date)) {
            $date = \Carbon\Carbon::parse($date);
        } elseif ($date instanceof \DateTime) {
            $date = \Carbon\Carbon::instance($date);
        }

        return $date->locale('id')->isoFormat('dddd, D MMMM YYYY');
    }

    /**
     * Format datetime untuk display dengan nama hari dan bulan
     */
    public static function formatDateTimeForDisplay(\DateTime|\Carbon\Carbon|string $datetime): string
    {
        if (is_string($datetime)) {
            $datetime = \Carbon\Carbon::parse($datetime);
        } elseif ($datetime instanceof \DateTime) {
            $datetime = \Carbon\Carbon::instance($datetime);
        }

        return $datetime->locale('id')->isoFormat('dddd, D MMMM YYYY HH:mm');
    }

    /**
     * Format relative time (2 jam yang lalu, dll)
     */
    public static function formatRelativeTime(\DateTime|\Carbon\Carbon|string $datetime): string
    {
        if (is_string($datetime)) {
            $datetime = \Carbon\Carbon::parse($datetime);
        } elseif ($datetime instanceof \DateTime) {
            $datetime = \Carbon\Carbon::instance($datetime);
        }

        return $datetime->locale('id')->diffForHumans();
    }

    // * ========================================
    // * SIZE FORMATTING
    // * ========================================

    /**
     * Format file size
     */
    public static function formatFileSize(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }

    // * ========================================
    // * STRING FORMATTING
    // * ========================================

    /**
     * Truncate text dengan ellipsis
     */
    public static function truncateText(string $text, int $length = 100, string $suffix = '...'): string
    {
        if (strlen($text) <= $length) {
            return $text;
        }

        return substr($text, 0, $length) . $suffix;
    }

    /**
     * Format phone number Indonesia
     */
    public static function formatPhoneNumber(string $phone): string
    {
        // Remove all non-numeric characters
        $phone = preg_replace('/[^0-9]/', '', $phone);

        // Add +62 if starts with 0
        if (substr($phone, 0, 1) === '0') {
            $phone = '+62' . substr($phone, 1);
        }

        // Add +62 if doesn't start with +62
        if (substr($phone, 0, 3) !== '+62') {
            $phone = '+62' . $phone;
        }

        return $phone;
    }

    /**
     * Format phone number untuk display
     */
    public static function formatPhoneForDisplay(string $phone): string
    {
        $phone = self::formatPhoneNumber($phone);

        // Format: +62 812-3456-7890
        if (strlen($phone) >= 10) {
            return preg_replace('/(\+62)(\d{3})(\d{4})(\d+)/', '$1 $2-$3-$4', $phone);
        }

        return $phone;
    }

    // * ========================================
    // * VALIDATION HELPERS
    // * ========================================

    /**
     * Check if value is empty untuk display
     */
    public static function displayIfNotEmpty($value, string $suffix = '', string $fallback = '-'): string
    {
        if (empty($value) || $value === 0 || $value === '0') {
            return $fallback;
        }

        return $value . $suffix;
    }

    /**
     * Format conditional text
     */
    public static function conditionalFormat($value, callable $formatter, string $fallback = '-'): string
    {
        if (empty($value) || $value === 0 || $value === '0') {
            return $fallback;
        }

        return $formatter($value);
    }

    // * ========================================
    // * COORDINATE FORMATTING
    // * ========================================

    /**
     * Format latitude/longitude coordinates
     */
    public static function formatCoordinates(float $lat, float $lng, int $precision = 6): string
    {
        return number_format($lat, $precision) . ', ' . number_format($lng, $precision);
    }

    /**
     * Generate Google Maps URL
     */
    public static function generateMapsUrl(float $lat, float $lng): string
    {
        return "https://www.google.com/maps?q={$lat},{$lng}";
    }

    // * ========================================
    // * BARCODE/ID GENERATION - CENTRALIZED
    // * ========================================

    /**
     * Generate barcode dengan prefix dan format tanggal
     * CENTRALIZED: Hapus dari helper lain, gunakan ini saja
     */
    public static function generateBarcode(string $prefix = 'BC', int $randomLength = 6): string
    {
        $date = now()->format('ymd');
        $random = strtoupper(\Illuminate\Support\Str::random($randomLength));

        return "{$prefix}{$date}{$random}";
    }

    /**
     * Generate order number dengan prefix dan format tanggal
     * CENTRALIZED: Hapus dari helper lain, gunakan ini saja
     */
    public static function generateOrderNumber(string $prefix = 'DO', int $randomLength = 4): string
    {
        $date = now()->format('ymd');
        $random = strtoupper(\Illuminate\Support\Str::random($randomLength));

        return "{$prefix}{$date}{$random}";
    }

    /**
     * Generate company code dengan prefix dan format
     * MOVED: Dari ClientHelper ke sini untuk konsistensi
     */
    public static function generateCompanyCode(string $prefix = 'CL', int $length = 6): string
    {
        $date = now()->format('ym');
        $random = strtoupper(\Illuminate\Support\Str::random($length));

        return "{$prefix}{$date}{$random}";
    }

    /**
     * Generate unique code dengan custom format
     * GENERAL: Method umum untuk generate kode apapun
     */
    public static function generateUniqueCode(string $prefix, int $randomLength = 6, string $dateFormat = 'ymd'): string
    {
        $date = now()->format($dateFormat);
        $random = strtoupper(\Illuminate\Support\Str::random($randomLength));

        return "{$prefix}{$date}{$random}";
    }

    /**
     * Format barcode untuk display (dengan spacing)
     */
    public static function formatBarcodeForDisplay(string $barcode): string
    {
        // Format: BC240627ABC123 -> BC-240627-ABC123
        if (strlen($barcode) >= 8) {
            return preg_replace('/([A-Z]{2})(\d{6})([A-Z0-9]+)/', '$1-$2-$3', $barcode);
        }

        return $barcode;
    }

    // * ========================================
    // * UI ICONS - CENTRALIZED
    // * ========================================

    /**
     * Get common UI icons untuk menghindari hardcode di component
     * CENTRALIZED: Semua icon UI umum di satu tempat
     */
    public static function getCommonIcons(): array
    {
        return [
            'search' => 'phosphor.magnifying-glass',
            'filter' => 'phosphor.funnel',
            'add' => 'phosphor.plus-circle',
            'edit' => 'phosphor.pencil',
            'delete' => 'phosphor.trash',
            'view' => 'phosphor.eye',
            'info' => 'phosphor.info',
            'warning' => 'phosphor.warning',
            'success' => 'phosphor.check-circle',
            'error' => 'phosphor.x-circle',
            'email' => 'phosphor.envelope-simple',
            'calendar' => 'phosphor.calendar',
            'menu' => 'phosphor.dots-three-vertical',
            'sort_asc' => 'phosphor.sort-ascending',
            'sort_desc' => 'phosphor.sort-descending',
            'reset' => 'phosphor.arrow-counter-clockwise',
            'back' => 'phosphor.arrow-left',
            'next' => 'phosphor.arrow-right',
            'close' => 'phosphor.x',
            'check' => 'phosphor.check',
            'upload' => 'phosphor.upload',
            'download' => 'phosphor.download',
        ];
    }

    /**
     * Get specific common icon
     */
    public static function getCommonIcon(string $key): string
    {
        return self::getCommonIcons()[$key] ?? 'phosphor.question';
    }
}
