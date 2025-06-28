<?php

namespace App\Class\Helper;

/**
 * Helper class untuk mengelola Client
 */
class ClientHelper
{

    // * ========================================
    // * CONFIGURATION CONSTANTS
    // * ========================================

    const DEFAULT_EMPTY_VALUE = '-';
    const DEFAULT_ICON = 'phosphor.question';
    const DEFAULT_COLOR = 'neutral';
    const COORDINATE_PRECISION = 8;
    const MAX_NEARBY_DISTANCE_KM = 50;

    // * ========================================
    // * TOAST MESSAGE CONSTANTS
    // * ========================================

    const TOAST_CLIENT_CREATED = 'Client berhasil dibuat!';
    const TOAST_CLIENT_UPDATED = 'Data client berhasil diperbarui!';
    const TOAST_CLIENT_DELETED = 'Client berhasil dihapus.';
    const TOAST_CLIENT_ADDED = 'Client berhasil ditambahkan.';
    const TOAST_FORM_RESET = 'Form berhasil direset.';
    const TOAST_STATUS_CHANGED_ACTIVE = 'Status client diubah ke aktif';
    const TOAST_STATUS_CHANGED_INACTIVE = 'Status client diubah ke nonaktif';
    const TOAST_FILTER_CLEARED = 'Filter berhasil dibersihkan.';

    // * ========================================
    // * PAGE TITLE & SUBTITLE CONSTANTS
    // * ========================================

    const PAGE_TITLE_INDEX = 'Data Client';
    const PAGE_SUBTITLE_INDEX = 'Kelola data client dan informasi perusahaan di sini';
    const PAGE_TITLE_CREATE = 'Tambah Client Baru';
    const PAGE_SUBTITLE_CREATE = 'Tambahkan data client baru beserta informasi perusahaan di sini';
    const PAGE_TITLE_EDIT = 'Edit Client';
    const PAGE_SUBTITLE_EDIT = 'Perbarui informasi client dan perusahaan';
    const PAGE_TITLE_VIEW = 'Detail Client';
    const PAGE_SUBTITLE_VIEW = 'Lihat informasi lengkap client';

    // * ========================================
    // * FORM MESSAGE CONSTANTS
    // * ========================================

    const FORM_READY = 'Siap untuk disimpan!';
    const FORM_INCOMPLETE = 'Lengkapi semua field wajib';
    const FORM_PREVIEW = 'Mulai mengisi form untuk preview';
    const FORM_CHANGED = 'Ada perubahan yang belum disimpan';
    const FORM_SAVED = 'Semua tersimpan';

    // * ========================================
    // * ERROR MESSAGE CONSTANTS
    // * ========================================

    const ERROR_NOT_CLIENT = 'User ini bukan client.';
    const ERROR_NOT_CLIENT_FULL = 'User ini bukan client yang valid.';
    const ERROR_SAVE_FAILED = 'Terjadi kesalahan saat menyimpan data';
    const ERROR_DELETE_FAILED = 'Terjadi kesalahan saat menghapus data';
    const ERROR_CREATE_FAILED = 'Terjadi kesalahan saat membuat client';

    // * ========================================
    // * EMPTY STATE CONSTANTS
    // * ========================================

    const EMPTY_NO_CLIENTS = 'Tidak ada client ditemukan';
    const EMPTY_NO_CLIENTS_DESC = 'Belum ada client yang terdaftar dalam sistem';
    const EMPTY_SEARCH_NO_RESULTS = 'Tidak ada client yang cocok dengan pencarian';

    // * ========================================
    // * STATISTICS LABEL CONSTANTS
    // * ========================================

    const STAT_TOTAL_CLIENTS = 'Total Client';
    const STAT_ACTIVE_CLIENTS = 'Client Aktif';
    const STAT_INACTIVE_CLIENTS = 'Client Nonaktif';


    // * ========================================
    // * FIELD UI MAPPINGS
    // * ========================================

    /**
     * Mapping icon untuk client form fields
     */
    private static array $clientFieldIcons = [
        'company_name' => 'phosphor.buildings',
        'company_code' => 'phosphor.identification-card',
        'company_address' => 'phosphor.map-pin',
        'phone' => 'phosphor.phone',
        'fax' => 'phosphor.printer',
        'tax_id' => 'phosphor.identification-card',
        'contact_person' => 'phosphor.user-circle',
        'contact_phone' => 'phosphor.device-mobile',
        'contact_email' => 'phosphor.envelope',
        'contact_position' => 'phosphor.briefcase',
        'coordinates' => 'phosphor.globe',
        'client_display' => 'phosphor.eye',
    ];

    /**
     * Mapping warna untuk client form fields
     */
    private static array $clientFieldColors = [
        'company_name' => 'primary',
        'company_code' => 'info',
        'company_address' => 'secondary',
        'phone' => 'success',
        'fax' => 'success',
        'tax_id' => 'warning',
        'contact_person' => 'info',
        'contact_phone' => 'success',
        'contact_email' => 'info',
        'contact_position' => 'secondary',
        'coordinates' => 'accent',
        'client_display' => 'info',
    ];


    // * ========================================
    // * FIELD UI METHODS
    // * ========================================

    /**
     * Get client field icon
     */
    public static function getClientFieldIcon(string $field): string
    {
        return self::$clientFieldIcons[$field] ?? self::DEFAULT_ICON;
    }

    /**
     * Get client field color
     */
    public static function getClientFieldColor(string $field): string
    {
        return self::$clientFieldColors[$field] ?? self::DEFAULT_COLOR;
    }


    // * ========================================
    // * UTILITY METHODS
    // * ========================================

    /**
     * Generate unique client code (delegated to FormatHelper)
     */
    public static function generateClientCode(): string
    {
        return FormatHelper::generateCompanyCode();
    }

    /**
     * Format company display name
     */
    public static function formatCompanyDisplayName(string $companyName, string $companyCode): string
    {
        return "{$companyName} ({$companyCode})";
    }

    /**
     * Format company address for display
     */
    public static function formatCompanyAddress(string $address, int $maxLength = 100): string
    {
        if (strlen($address) <= $maxLength) {
            return $address;
        }

        return substr($address, 0, $maxLength - 3) . '...';
    }

    /**
     * Validate tax ID (NPWP) format
     */
    public static function isValidTaxId(?string $taxId): bool
    {
        if (empty($taxId)) {
            return true; // nullable field
        }

        // NPWP format: XX.XXX.XXX.X-XXX.XXX
        $pattern = '/^\d{2}\.\d{3}\.\d{3}\.\d{1}-\d{3}\.\d{3}$/';
        return preg_match($pattern, $taxId) === 1;
    }

    /**
     * Format tax ID untuk display
     */
    public static function formatTaxIdForDisplay(?string $taxId): string
    {
        if (empty($taxId)) {
            return self::DEFAULT_EMPTY_VALUE;
        }

        // Remove dots and dashes, then reformat
        $clean = preg_replace('/[^0-9]/', '', $taxId);

        if (strlen($clean) === 15) {
            return substr($clean, 0, 2) . '.' .
                   substr($clean, 2, 3) . '.' .
                   substr($clean, 5, 3) . '.' .
                   substr($clean, 8, 1) . '-' .
                   substr($clean, 9, 3) . '.' .
                   substr($clean, 12, 3);
        }

        return $taxId;
    }

    /**
     * Validate Indonesian coordinates
     */
    public static function isValidIndonesianCoordinates(?float $lat, ?float $lng): bool
    {
        if (is_null($lat) || is_null($lng)) {
            return true; // nullable fields
        }

        // Indonesia coordinate bounds (approximate)
        $latMin = -11.0; // Southern boundary
        $latMax = 6.0;   // Northern boundary
        $lngMin = 95.0;  // Western boundary
        $lngMax = 141.0; // Eastern boundary

        return ($lat >= $latMin && $lat <= $latMax && $lng >= $lngMin && $lng <= $lngMax);
    }

}
