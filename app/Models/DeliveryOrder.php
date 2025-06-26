<?php

namespace App\Models;

use App\Class\StatusHelper;
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

    /**
     * Get the attributes that should be cast (Laravel 12.x style)
     */
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

    /**
     * Relasi ke client penerima
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    /**
     * Relasi ke petugas lapangan yang membuat delivery order
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Relasi ke driver yang ditugaskan
     */
    public function driver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'driver_id');
    }

    /**
     * Relasi ke petugas ruangan yang verifikasi
     */
    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    /**
     * Relasi ke petugas gudang yang menyelesaikan
     */
    public function completedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'completed_by');
    }

    /**
     * Relasi ke items dalam delivery order
     */
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
            StatusHelper::DO_STATUS_LOADING,
            StatusHelper::DO_STATUS_VERIFIED,
            StatusHelper::DO_STATUS_DISPATCHED,
            StatusHelper::DO_STATUS_ARRIVED
        ]);
    }

    public function scopeCompletedOrders($query)
    {
        return $query->where('status', StatusHelper::DO_STATUS_COMPLETED);
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
                $deliveryOrder->order_number = StatusHelper::generateOrderNumber('DO');
            }

            if (empty($deliveryOrder->barcode_do)) {
                $deliveryOrder->barcode_do = StatusHelper::generateBarcode('BC');
            }
        });
    }

    // * ========================================
    // * BUSINESS LOGIC METHODS
    // * ========================================

    /**
     * Cek apakah delivery order memiliki discrepancy
     */
    public function hasDiscrepancies(): bool
    {
        return $this->has_discrepancy ||
            $this->items()->whereColumn('received_quantity', '!=', 'sent_quantity')->exists();
    }

    // * ========================================
    // * ACCESSORS (Laravel 12.x Attribute Style)
    // * ========================================

    /**
     * Get status label dalam bahasa Indonesia menggunakan StatusHelper
     */
    protected function statusLabel(): Attribute
    {
        return Attribute::make(
            get: fn() => StatusHelper::getDeliveryOrderStatusLabel($this->status),
        );
    }

    /**
     * Get status color menggunakan StatusHelper
     */
    protected function statusColor(): Attribute
    {
        return Attribute::make(
            get: fn() => StatusHelper::getDeliveryOrderStatusColor($this->status),
        );
    }

    /**
     * Get progress percentage menggunakan StatusHelper
     */
    protected function progressPercentage(): Attribute
    {
        return Attribute::make(
            get: fn() => StatusHelper::getDeliveryOrderProgressPercentage($this->status),
        );
    }

    /**
     * Get total items dalam delivery order
     */
    protected function totalItems(): Attribute
    {
        return Attribute::make(
            get: fn() => (int) $this->items()->sum('sent_quantity'),
        );
    }

    /**
     * Get total berat dalam delivery order
     */
    protected function totalWeight(): Attribute
    {
        return Attribute::make(
            get: fn() => (float) $this->items()->selectRaw('SUM(sent_quantity * COALESCE(weight, 0)) as total')->first()->total ?? 0,
        );
    }

    /**
     * Get total nilai dalam delivery order
     */
    protected function totalValue(): Attribute
    {
        return Attribute::make(
            get: fn() => (float) $this->items()->sum('total_value') ?? 0,
        );
    }

    /**
     * Get formatted total value menggunakan StatusHelper
     */
    protected function formattedTotalValue(): Attribute
    {
        return Attribute::make(
            get: fn() => StatusHelper::formatRupiah($this->total_value),
        );
    }

    /**
     * Get formatted total weight menggunakan StatusHelper
     */
    protected function formattedTotalWeight(): Attribute
    {
        return Attribute::make(
            get: fn() => StatusHelper::formatWeight($this->total_weight),
        );
    }

    /**
     * Get Google Maps URL berdasarkan koordinat tujuan
     */
    protected function mapUrl(): Attribute
    {
        return Attribute::make(
            get: function () {
                if (!$this->destination_latitude || !$this->destination_longitude) {
                    return null;
                }

                return "https://www.google.com/maps?q={$this->destination_latitude},{$this->destination_longitude}";
            }
        );
    }

    // * ========================================
    // * STATIC HELPER METHODS
    // * ========================================

    /**
     * Get all available statuses untuk dropdown menggunakan StatusHelper
     */
    public static function getAllStatuses(): array
    {
        return StatusHelper::getAllDeliveryOrderStatuses();
    }

    /**
     * Get status color by status key menggunakan StatusHelper
     */
    public static function getStatusColorByKey(string $status): string
    {
        return StatusHelper::getDeliveryOrderStatusColor($status);
    }

    /**
     * Get status label by status key menggunakan StatusHelper
     */
    public static function getStatusLabelByKey(string $status): string
    {
        return StatusHelper::getDeliveryOrderStatusLabel($status);
    }
}
