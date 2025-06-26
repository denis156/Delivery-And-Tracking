<?php

namespace App\Models;

use App\Class\Helper\DriverHelper;
use App\Class\Helper\FormatHelper;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Driver extends Model
{
    use HasFactory, SoftDeletes;

    // * ========================================
    // * KONFIGURASI MODEL
    // * ========================================

    protected $fillable = [
        'user_id',
        'license_number',
        'license_type',
        'license_expiry',
        'phone',
        'address',
        'vehicle_type',
        'vehicle_plate',
    ];

    protected function casts(): array
    {
        return [
            'license_expiry' => 'date',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }

    // * ========================================
    // * RELATIONSHIPS
    // * ========================================

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function deliveryOrders(): HasMany
    {
        return $this->hasMany(DeliveryOrder::class, 'driver_id', 'user_id');
    }

    // * ========================================
    // * QUERY SCOPES
    // * ========================================

    public function scopeByLicenseType($query, string $licenseType)
    {
        return $query->where('license_type', $licenseType);
    }

    public function scopeExpiredLicense($query)
    {
        return $query->where('license_expiry', '<', now());
    }

    public function scopeExpiringSoon($query, int $days = 30)
    {
        return $query->whereBetween('license_expiry', [now(), now()->addDays($days)]);
    }

    public function scopeValidLicense($query)
    {
        return $query->where('license_expiry', '>', now());
    }

    // * ========================================
    // * BUSINESS LOGIC METHODS
    // * ========================================

    public function isLicenseExpired(): bool
    {
        return DriverHelper::isLicenseExpired($this->license_expiry);
    }

    public function isLicenseExpiringSoon(): bool
    {
        return DriverHelper::isLicenseExpiringSoon($this->license_expiry);
    }

    public function hasValidLicense(): bool
    {
        return !$this->isLicenseExpired();
    }

    // * ========================================
    // * ACCESSORS (Laravel 12.x Attribute Style)
    // * ========================================

    protected function licenseLabel(): Attribute
    {
        return Attribute::make(
            get: fn() => DriverHelper::getLicenseLabel($this->license_type),
        );
    }

    protected function licenseColor(): Attribute
    {
        return Attribute::make(
            get: fn() => DriverHelper::getLicenseColor($this->license_type),
        );
    }

    protected function formattedLicenseNumber(): Attribute
    {
        return Attribute::make(
            get: fn() => DriverHelper::formatLicenseNumber($this->license_number),
        );
    }

    protected function formattedVehiclePlate(): Attribute
    {
        return Attribute::make(
            get: fn() => DriverHelper::formatVehiclePlate($this->vehicle_plate),
        );
    }

    protected function formattedPhone(): Attribute
    {
        return Attribute::make(
            get: fn() => FormatHelper::formatPhoneForDisplay($this->phone),
        );
    }

    protected function licenseStatus(): Attribute
    {
        return Attribute::make(
            get: fn() => DriverHelper::getLicenseStatus($this->license_expiry),
        );
    }

    protected function formattedLicenseExpiry(): Attribute
    {
        return Attribute::make(
            get: fn() => FormatHelper::formatDate($this->license_expiry),
        );
    }

    protected function driverDisplayName(): Attribute
    {
        return Attribute::make(
            get: fn() => DriverHelper::formatDriverDisplayName(
                $this->user->name ?? 'Unknown',
                $this->vehicle_type,
                $this->vehicle_plate
            ),
        );
    }

    // * ========================================
    // * STATIC HELPER METHODS
    // * ========================================

    public static function getAllLicenseTypes(): array
    {
        return DriverHelper::getAllLicenseTypes();
    }

    public static function isValidLicenseType(string $licenseType): bool
    {
        return DriverHelper::isValidLicenseType($licenseType);
    }
}
