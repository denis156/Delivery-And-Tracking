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
}
