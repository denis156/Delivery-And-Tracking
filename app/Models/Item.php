<?php

namespace App\Models;

use App\Class\StatusHelper;
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

    public function scopeWithDiscrepancy($query)
    {
        return $query->whereColumn('received_quantity', '!=', 'sent_quantity');
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
    // * BUSINESS LOGIC METHODS
    // * ========================================

    /**
     * Cek apakah item memiliki discrepancy
     */
    public function hasDiscrepancy(): bool
    {
        return $this->received_quantity && $this->received_quantity != $this->sent_quantity;
    }

    /**
     * Get deskripsi discrepancy
     */
    public function getDiscrepancyDescription(): ?string
    {
        if (!$this->hasDiscrepancy()) {
            return null;
        }

        $difference = $this->received_quantity - $this->sent_quantity;
        $type = $difference > 0 ? 'Kelebihan' : 'Kekurangan';
        $amount = abs($difference);

        return "{$type}: {$amount} {$this->unit}";
    }

    // * ========================================
    // * ACCESSORS (Laravel 12.x Attribute Style)
    // * ========================================

    /**
     * Get status label menggunakan StatusHelper
     */
    protected function statusLabel(): Attribute
    {
        return Attribute::make(
            get: fn() => StatusHelper::getItemStatusLabel($this->status),
        );
    }

    /**
     * Get status color menggunakan StatusHelper
     */
    protected function statusColor(): Attribute
    {
        return Attribute::make(
            get: fn() => StatusHelper::getItemStatusColor($this->status),
        );
    }

    /**
     * Get condition label menggunakan StatusHelper
     */
    protected function conditionLabel(): Attribute
    {
        return Attribute::make(
            get: fn() => StatusHelper::getConditionLabel($this->condition),
        );
    }

    /**
     * Get condition color menggunakan StatusHelper
     */
    protected function conditionColor(): Attribute
    {
        return Attribute::make(
            get: fn() => StatusHelper::getConditionColor($this->condition),
        );
    }

    /**
     * Get formatted weight menggunakan StatusHelper
     */
    protected function formattedWeight(): Attribute
    {
        return Attribute::make(
            get: function () {
                if (!$this->weight) return '-';

                $totalWeight = $this->weight * $this->sent_quantity;
                return number_format($this->weight, 1) . ' kg/unit (' . StatusHelper::formatWeight($totalWeight) . ' total)';
            }
        );
    }

    /**
     * Get formatted value menggunakan StatusHelper
     */
    protected function formattedValue(): Attribute
    {
        return Attribute::make(
            get: function () {
                if (!$this->total_value) return '-';

                return StatusHelper::formatRupiah($this->total_value);
            }
        );
    }

    /**
     * Get formatted unit value menggunakan StatusHelper
     */
    protected function formattedUnitValue(): Attribute
    {
        return Attribute::make(
            get: function () {
                if (!$this->unit_value) return '-';

                return StatusHelper::formatRupiah($this->unit_value);
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
     * Get formatted sent quantity menggunakan StatusHelper
     */
    protected function formattedSentQuantity(): Attribute
    {
        return Attribute::make(
            get: fn() => StatusHelper::formatQuantity($this->sent_quantity, $this->unit),
        );
    }

    /**
     * Get formatted received quantity menggunakan StatusHelper
     */
    protected function formattedReceivedQuantity(): Attribute
    {
        return Attribute::make(
            get: function () {
                if (!$this->received_quantity) return '-';

                return StatusHelper::formatQuantity($this->received_quantity, $this->unit);
            }
        );
    }

    // * ========================================
    // * STATIC HELPER METHODS
    // * ========================================

    /**
     * Get all available statuses menggunakan StatusHelper
     */
    public static function getAllStatuses(): array
    {
        return StatusHelper::getAllItemStatuses();
    }

    /**
     * Get all available conditions menggunakan StatusHelper
     */
    public static function getAllConditions(): array
    {
        return StatusHelper::getAllConditions();
    }

    /**
     * Get status color by status key menggunakan StatusHelper
     */
    public static function getStatusColorByKey(string $status): string
    {
        return StatusHelper::getItemStatusColor($status);
    }

    /**
     * Get status label by status key menggunakan StatusHelper
     */
    public static function getStatusLabelByKey(string $status): string
    {
        return StatusHelper::getItemStatusLabel($status);
    }

    /**
     * Get condition color by condition key menggunakan StatusHelper
     */
    public static function getConditionColorByKey(string $condition): string
    {
        return StatusHelper::getConditionColor($condition);
    }

    /**
     * Get condition label by condition key menggunakan StatusHelper
     */
    public static function getConditionLabelByKey(string $condition): string
    {
        return StatusHelper::getConditionLabel($condition);
    }
}
