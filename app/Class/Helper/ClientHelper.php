<?php

namespace App\Class\Helper;

/**
 * Helper class untuk mengelola Client
 */
class ClientHelper
{
    // * ========================================
    // * UTILITY METHODS
    // * ========================================

    /**
     * Format company display name
     */
    public static function formatCompanyDisplayName(string $companyName, string $companyCode): string
    {
        return "{$companyName} ({$companyCode})";
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
            return '-';
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
}
