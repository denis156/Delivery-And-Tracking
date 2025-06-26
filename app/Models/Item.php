<?php

namespace App\Models;

use App\Class\Helper\ItemHelper;
use App\Class\Helper\FormatHelper;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Item extends Model
{
    use HasFactory, SoftDeletes;

    // * ========================================
    // * KONFIGURASI MODEL
    // * ========================================

    protected $fillable = [
        'delivery_order_id',
        'name',
        'description',
        'category',
        'unit',
        'sent_quantity',
        'received_quantity',
        'weight',
        'unit_value',
        'total_value',
        'status',
        'condition',
        'notes',
        'discrepancy_notes',
        'barcode_item',
        'sort_order',
    ];

    /**
     * Get the attributes that should be cast (Laravel 12.x style)
     */
    protected function casts(): array
    {
        return [
            'sent_quantity' => 'decimal:2',
            'received_quantity' => 'decimal:2',
            'weight' => 'decimal:2',
            'unit_value' => 'decimal:2',
            'total_value' => 'decimal:2',
            'sort_order' => 'integer',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }

    // * ========================================
    // * RELATIONSHIPS
    // * ========================================

    /**
     * Relasi ke delivery order
     */
    public function deliveryOrder(): BelongsTo
    {
        return $this->belongsTo(DeliveryOrder::class);
    }

    // * ========================================
    // * QUERY SCOPES
    // * ========================================

    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    public function scopeByCondition($query, string $condition)
    {
        return $query->where('condition', $condition);
    }

    /**
     * DIPERBAIKI: Tambah null check untuk discrepancy scope
     */
    public function scopeWithDiscrepancy($query)
    {
        return $query->whereNotNull('received_quantity')
                    ->whereNotNull('sent_quantity')
                    ->whereColumn('received_quantity', '!=', 'sent_quantity');
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }

    // * ========================================
    // * MODEL EVENTS
    // * ========================================

    protected static function booted(): void
    {
        static::creating(function (Item $item) {
            if (is_null($item->sort_order)) {
                $maxOrder = static::where('delivery_order_id', $item->delivery_order_id)
                    ->max('sort_order') ?? 0;
                $item->sort_order = $maxOrder + 1;
            }

            // Auto generate barcode if empty
            if (empty($item->barcode_item)) {
                $item->barcode_item = FormatHelper::generateBarcode('BC');
            }

            // Auto calculate total value
            if ($item->unit_value && $item->sent_quantity) {
                $item->total_value = $item->unit_value * $item->sent_quantity;
            }
        });

        static::updating(function (Item $item) {
            // Recalculate total value if unit_value or quantity changed
            if ($item->isDirty(['unit_value', 'sent_quantity']) && $item->unit_value && $item->sent_quantity) {
                $item->total_value = $item->unit_value * $item->sent_quantity;
            }
        });
    }

    // * ========================================
    // * BUSINESS LOGIC METHODS - DIPERBAIKI
    // * ========================================

    /**
     * Cek apakah item memiliki discrepancy
     * DIPERBAIKI: Tambah null check
     */
    public function hasDiscrepancy(): bool
    {
        // Jika received_quantity null, belum ada discrepancy
        if (is_null($this->received_quantity)) {
            return false;
        }

        return $this->received_quantity != $this->sent_quantity;
    }

    /**
     * Get deskripsi discrepancy
     * DIPERBAIKI: Null safety yang robust
     */
    public function getDiscrepancyDescription(): ?string
    {
        // Jika received_quantity null, belum ada data untuk discrepancy
        if (is_null($this->received_quantity)) {
            return null;
        }

        // Jika tidak ada discrepancy
        if (!$this->hasDiscrepancy()) {
            return null;
        }

        $difference = $this->received_quantity - $this->sent_quantity;
        $type = $difference > 0 ? 'Kelebihan' : 'Kekurangan';
        $amount = abs($difference);

        return "{$type}: {$amount} {$this->unit}";
    }

    /**
     * Get discrepancy percentage
     */
    public function getDiscrepancyPercentage(): ?float
    {
        if (is_null($this->received_quantity) || $this->sent_quantity == 0) {
            return null;
        }

        $difference = abs($this->received_quantity - $this->sent_quantity);
        return ($difference / $this->sent_quantity) * 100;
    }

    /**
     * Check if item is completely missing (received = 0)
     */
    public function isCompletelyMissing(): bool
    {
        return $this->received_quantity === 0.0;
    }

    /**
     * Check if item has excess quantity
     */
    public function hasExcess(): bool
    {
        if (is_null($this->received_quantity)) {
            return false;
        }

        return $this->received_quantity > $this->sent_quantity;
    }

    /**
     * Check if item has shortage
     */
    public function hasShortage(): bool
    {
        if (is_null($this->received_quantity)) {
            return false;
        }

        return $this->received_quantity < $this->sent_quantity;
    }

    // * ========================================
    // * ACCESSORS (Laravel 12.x Attribute Style)
    // * ========================================

    /**
     * Get status label menggunakan ItemHelper
     */
    protected function statusLabel(): Attribute
    {
        return Attribute::make(
            get: fn() => ItemHelper::getStatusLabel($this->status),
        );
    }

    /**
     * Get status color menggunakan ItemHelper
     */
    protected function statusColor(): Attribute
    {
        return Attribute::make(
            get: fn() => ItemHelper::getStatusColor($this->status),
        );
    }

    /**
     * Get condition label menggunakan ItemHelper
     */
    protected function conditionLabel(): Attribute
    {
        return Attribute::make(
            get: fn() => ItemHelper::getConditionLabel($this->condition),
        );
    }

    /**
     * Get condition color menggunakan ItemHelper
     */
    protected function conditionColor(): Attribute
    {
        return Attribute::make(
            get: fn() => ItemHelper::getConditionColor($this->condition),
        );
    }

    /**
     * Get formatted weight menggunakan FormatHelper
     */
    protected function formattedWeight(): Attribute
    {
        return Attribute::make(
            get: function () {
                if (!$this->weight) return '-';

                $totalWeight = $this->weight * $this->sent_quantity;
                return number_format($this->weight, 1) . ' kg/unit (' . FormatHelper::formatWeight($totalWeight) . ' total)';
            }
        );
    }

    /**
     * Get formatted value menggunakan FormatHelper
     */
    protected function formattedValue(): Attribute
    {
        return Attribute::make(
            get: function () {
                if (!$this->total_value) return '-';

                return FormatHelper::formatRupiah($this->total_value);
            }
        );
    }

    /**
     * Get formatted unit value menggunakan FormatHelper
     */
    protected function formattedUnitValue(): Attribute
    {
        return Attribute::make(
            get: function () {
                if (!$this->unit_value) return '-';

                return FormatHelper::formatRupiah($this->unit_value);
            }
        );
    }

    /**
     * Get total weight untuk item ini
     */
    protected function totalWeight(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->weight ? ($this->weight * $this->sent_quantity) : 0,
        );
    }

    /**
     * Get formatted sent quantity menggunakan FormatHelper
     */
    protected function formattedSentQuantity(): Attribute
    {
        return Attribute::make(
            get: fn() => FormatHelper::formatQuantity($this->sent_quantity, $this->unit),
        );
    }

    /**
     * Get formatted received quantity menggunakan FormatHelper
     */
    protected function formattedReceivedQuantity(): Attribute
    {
        return Attribute::make(
            get: function () {
                if (!$this->received_quantity) return '-';

                return FormatHelper::formatQuantity($this->received_quantity, $this->unit);
            }
        );
    }

    // * ========================================
    // * STATIC HELPER METHODS
    // * ========================================

    /**
     * Get all available statuses menggunakan ItemHelper
     */
    public static function getAllStatuses(): array
    {
        return ItemHelper::getAllStatuses();
    }

    /**
     * Get all available conditions menggunakan ItemHelper
     */
    public static function getAllConditions(): array
    {
        return ItemHelper::getAllConditions();
    }

    /**
     * Get status color by status key menggunakan ItemHelper
     */
    public static function getStatusColorByKey(string $status): string
    {
        return ItemHelper::getStatusColor($status);
    }

    /**
     * Get status label by status key menggunakan ItemHelper
     */
    public static function getStatusLabelByKey(string $status): string
    {
        return ItemHelper::getStatusLabel($status);
    }

    /**
     * Get condition color by condition key menggunakan ItemHelper
     */
    public static function getConditionColorByKey(string $condition): string
    {
        return ItemHelper::getConditionColor($condition);
    }

    /**
     * Get condition label by condition key menggunakan ItemHelper
     */
    public static function getConditionLabelByKey(string $condition): string
    {
        return ItemHelper::getConditionLabel($condition);
    }

    /**
     * Validate status menggunakan ItemHelper
     */
    public static function isValidStatus(string $status): bool
    {
        return ItemHelper::isValidStatus($status);
    }

    /**
     * Validate condition menggunakan ItemHelper
     */
    public static function isValidCondition(string $condition): bool
    {
        return ItemHelper::isValidCondition($condition);
    }
}
