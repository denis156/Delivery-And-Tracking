<?php

namespace App\Models;

use App\Class\Helper\ClientHelper;
use App\Class\Helper\FormatHelper;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Client extends Model
{
    use HasFactory, SoftDeletes;

    // * ========================================
    // * KONFIGURASI MODEL
    // * ========================================

    protected $fillable = [
        'user_id',
        'company_name',
        'company_code',
        'company_address',
        'phone',
        'fax',
        'tax_id',
        'contact_person',
        'contact_phone',
        'contact_email',
        'contact_position',
        'company_latitude',
        'company_longitude',
    ];

    protected function casts(): array
    {
        return [
            'company_latitude' => 'decimal:8',
            'company_longitude' => 'decimal:8',
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
        return $this->hasMany(DeliveryOrder::class, 'client_id', 'user_id');
    }

    // * ========================================
    // * QUERY SCOPES
    // * ========================================

    public function scopeByCompanyCode($query, string $code)
    {
        return $query->where('company_code', $code);
    }

    public function scopeWithCoordinates($query)
    {
        return $query->whereNotNull('company_latitude')
                    ->whereNotNull('company_longitude');
    }

    public function scopeActiveClients($query)
    {
        return $query->whereHas('user', function ($q) {
            $q->where('is_active', true);
        });
    }

    public function scopeNearby($query, float $lat, float $lng, int $radiusKm = 50)
    {
        return $query->selectRaw("
                *,
                (6371 * acos(cos(radians(?)) * cos(radians(company_latitude)) * cos(radians(company_longitude) - radians(?)) + sin(radians(?)) * sin(radians(company_latitude)))) AS distance
            ", [$lat, $lng, $lat])
            ->having('distance', '<', $radiusKm)
            ->orderBy('distance');
    }

    // * ========================================
    // * MODEL EVENTS
    // * ========================================

    protected static function booted(): void
    {
        static::creating(function (Client $client) {
            if (empty($client->company_code)) {
                $client->company_code = FormatHelper::generateCompanyCode();
            }
        });
    }

    // * ========================================
    // * ACCESSORS (Laravel 12.x Attribute Style)
    // * ========================================

    protected function formattedTaxId(): Attribute
    {
        return Attribute::make(
            get: fn() => ClientHelper::formatTaxIdForDisplay($this->tax_id),
        );
    }

    protected function companyDisplayName(): Attribute
    {
        return Attribute::make(
            get: fn() => ClientHelper::formatCompanyDisplayName($this->company_name, $this->company_code),
        );
    }

    protected function formattedPhone(): Attribute
    {
        return Attribute::make(
            get: fn() => FormatHelper::formatPhoneForDisplay($this->phone),
        );
    }

    protected function formattedContactPhone(): Attribute
    {
        return Attribute::make(
            get: fn() => FormatHelper::formatPhoneForDisplay($this->contact_phone),
        );
    }

    protected function formattedCoordinates(): Attribute
    {
        return Attribute::make(
            get: function () {
                if (!$this->company_latitude || !$this->company_longitude) {
                    return '-';
                }

                return FormatHelper::formatCoordinates($this->company_latitude, $this->company_longitude);
            }
        );
    }

    protected function mapUrl(): Attribute
    {
        return Attribute::make(
            get: function () {
                if (!$this->company_latitude || !$this->company_longitude) {
                    return null;
                }

                return FormatHelper::generateMapsUrl($this->company_latitude, $this->company_longitude);
            }
        );
    }

    // * ========================================
    // * CLIENT STATUS ACCESSORS (via User relationship)
    // * ========================================

    protected function isActiveClient(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->user?->is_active ?? false,
        );
    }

    protected function clientStatusColor(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->user?->is_active ? 'success' : 'warning',
        );
    }

    protected function clientStatusLabel(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->user?->is_active ? 'Aktif' : 'Nonaktif',
        );
    }

    protected function clientStatusIcon(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->user?->is_active ? 'phosphor.check-circle' : 'phosphor.x-circle',
        );
    }

    protected function shortAddress(): Attribute
    {
        return Attribute::make(
            get: fn() => ClientHelper::formatCompanyAddress($this->company_address ?? '', 50),
        );
    }


    // * ========================================
    // * BUSINESS LOGIC METHODS
    // * ========================================

    public function isActive(): bool
    {
        return $this->user?->is_active ?? false;
    }

    public function canReceiveDeliveries(): bool
    {
        return $this->user?->is_active ?? false;
    }

    public function hasValidCoordinates(): bool
    {
        return ClientHelper::isValidIndonesianCoordinates($this->company_latitude, $this->company_longitude);
    }

    public function distanceFrom(float $lat, float $lng): ?float
    {
        if (!$this->company_latitude || !$this->company_longitude) {
            return null;
        }

        // Using Haversine formula for distance calculation
        $earthRadius = 6371; // km

        $dLat = deg2rad($lat - $this->company_latitude);
        $dLng = deg2rad($lng - $this->company_longitude);

        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($this->company_latitude)) * cos(deg2rad($lat)) *
             sin($dLng / 2) * sin($dLng / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

    /**
     * Check if company code is unique
     */
    public static function isUniqueCompanyCode(string $code, ?int $excludeId = null): bool
    {
        $query = static::where('company_code', $code);

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->count() === 0;
    }
}
