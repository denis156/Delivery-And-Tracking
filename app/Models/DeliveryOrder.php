<?php

namespace App\Models;

use App\Class\Helper\DeliveryOrderHelper;
use App\Class\Helper\FormatHelper;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DeliveryOrder extends Model
{
    use HasFactory, SoftDeletes;

    // * ========================================
    // * KONFIGURASI MODEL
    // * ========================================

    protected $fillable = [
        'order_number',
        'barcode_do',
        'status',
        'client_id',
        'delivery_address',
        'destination_latitude',
        'destination_longitude',
        'created_by',
        'driver_id',
        'verified_by',
        'completed_by',
        'loading_started_at',
        'verified_at',
        'dispatched_at',
        'arrived_at',
        'completed_at',
        'notes',
        'completion_notes',
        'has_discrepancy',
        'discrepancy_notes',
    ];

    protected function casts(): array
    {
        return [
            'loading_started_at' => 'datetime',
            'verified_at' => 'datetime',
            'dispatched_at' => 'datetime',
            'arrived_at' => 'datetime',
            'completed_at' => 'datetime',
            'destination_latitude' => 'decimal:8',
            'destination_longitude' => 'decimal:8',
            'has_discrepancy' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }

    // * ========================================
    // * RELATIONSHIPS
    // * ========================================

    public function client(): BelongsTo
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'driver_id');
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function completedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'completed_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(Item::class)->orderBy('sort_order');
    }

    // * ========================================
    // * QUERY SCOPES
    // * ========================================

    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByDriver($query, int $driverId)
    {
        return $query->where('driver_id', $driverId);
    }

    public function scopeByCreator($query, int $userId)
    {
        return $query->where('created_by', $userId);
    }

    public function scopeByClient($query, int $clientId)
    {
        return $query->where('client_id', $clientId);
    }

    public function scopeActiveOrders($query)
    {
        return $query->whereIn('status', [
            DeliveryOrderHelper::STATUS_LOADING,
            DeliveryOrderHelper::STATUS_VERIFIED,
            DeliveryOrderHelper::STATUS_DISPATCHED,
            DeliveryOrderHelper::STATUS_ARRIVED
        ]);
    }

    public function scopeCompletedOrders($query)
    {
        return $query->where('status', DeliveryOrderHelper::STATUS_COMPLETED);
    }

    public function scopeWithDiscrepancy($query)
    {
        return $query->where('has_discrepancy', true);
    }

    public function scopeToday($query)
    {
        return $query->whereDate('created_at', today());
    }

    // * ========================================
    // * MODEL EVENTS
    // * ========================================

    protected static function booted(): void
    {
        static::creating(function (DeliveryOrder $deliveryOrder) {
            if (empty($deliveryOrder->order_number)) {
                $deliveryOrder->order_number = FormatHelper::generateOrderNumber('DO');
            }

            if (empty($deliveryOrder->barcode_do)) {
                $deliveryOrder->barcode_do = FormatHelper::generateBarcode('BC');
            }
        });
    }

    // * ========================================
    // * BUSINESS LOGIC METHODS - DIPERBAIKI
    // * ========================================

    /**
     * Cek apakah delivery order memiliki discrepancy
     * DIPERBAIKI: Tambah null check yang robust
     */
    public function hasDiscrepancies(): bool
    {
        // Cek flag discrepancy yang sudah ada
        if ($this->has_discrepancy) {
            return true;
        }

        // Cek discrepancy di level item dengan null safety
        return $this->items()
            ->whereNotNull('received_quantity')
            ->whereNotNull('sent_quantity')
            ->whereColumn('received_quantity', '!=', 'sent_quantity')
            ->exists();
    }

    /**
     * Get total discrepancy items
     */
    public function getTotalDiscrepancyItems(): int
    {
        return $this->items()
            ->whereNotNull('received_quantity')
            ->whereNotNull('sent_quantity')
            ->whereColumn('received_quantity', '!=', 'sent_quantity')
            ->count();
    }

    /**
     * Get items with discrepancy
     */
    public function getDiscrepancyItems()
    {
        return $this->items()
            ->whereNotNull('received_quantity')
            ->whereNotNull('sent_quantity')
            ->whereColumn('received_quantity', '!=', 'sent_quantity')
            ->get();
    }

    // * ========================================
    // * ACCESSORS (Laravel 12.x Attribute Style)
    // * ========================================

    protected function statusLabel(): Attribute
    {
        return Attribute::make(
            get: fn() => DeliveryOrderHelper::getStatusLabel($this->status),
        );
    }

    protected function statusColor(): Attribute
    {
        return Attribute::make(
            get: fn() => DeliveryOrderHelper::getStatusColor($this->status),
        );
    }

    protected function progressPercentage(): Attribute
    {
        return Attribute::make(
            get: fn() => DeliveryOrderHelper::getProgressPercentage($this->status),
        );
    }

    protected function totalItems(): Attribute
    {
        return Attribute::make(
            get: fn() => (int) $this->items()->sum('sent_quantity'),
        );
    }

    protected function totalWeight(): Attribute
    {
        return Attribute::make(
            get: fn() => (float) $this->items()->selectRaw('SUM(sent_quantity * COALESCE(weight, 0)) as total')->first()->total ?? 0,
        );
    }

    protected function totalValue(): Attribute
    {
        return Attribute::make(
            get: fn() => (float) $this->items()->sum('total_value') ?? 0,
        );
    }

    protected function formattedTotalValue(): Attribute
    {
        return Attribute::make(
            get: fn() => FormatHelper::formatRupiah($this->total_value),
        );
    }

    protected function formattedTotalWeight(): Attribute
    {
        return Attribute::make(
            get: fn() => FormatHelper::formatWeight($this->total_weight),
        );
    }

    protected function mapUrl(): Attribute
    {
        return Attribute::make(
            get: function () {
                if (!$this->destination_latitude || !$this->destination_longitude) {
                    return null;
                }

                return FormatHelper::generateMapsUrl($this->destination_latitude, $this->destination_longitude);
            }
        );
    }

    // * ========================================
    // * STATIC HELPER METHODS
    // * ========================================

    public static function getAllStatuses(): array
    {
        return DeliveryOrderHelper::getAllStatuses();
    }

    public static function getStatusColorByKey(string $status): string
    {
        return DeliveryOrderHelper::getStatusColor($status);
    }

    public static function getStatusLabelByKey(string $status): string
    {
        return DeliveryOrderHelper::getStatusLabel($status);
    }

    public static function isValidStatus(string $status): bool
    {
        return DeliveryOrderHelper::isValidStatus($status);
    }
}
