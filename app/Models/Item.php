<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Item extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'delivery_order_id',
        'name',
        'description',
        'category',
        'unit',
        'planned_quantity',
        'actual_quantity',
        'weight',
        'length',
        'width',
        'height',
        'unit_value',
        'total_value',
        'is_insured',
        'condition_sent',
        'condition_received',
        'is_fragile',
        'requires_cold_storage',
        'status',
        'notes',
        'damage_notes',
        'barcode',
        'serial_number',
        'sort_order',
    ];

    protected $casts = [
        'planned_quantity' => 'decimal:2',
        'actual_quantity' => 'decimal:2',
        'weight' => 'decimal:2',
        'length' => 'decimal:2',
        'width' => 'decimal:2',
        'height' => 'decimal:2',
        'unit_value' => 'decimal:2',
        'total_value' => 'decimal:2',
        'is_insured' => 'boolean',
        'is_fragile' => 'boolean',
        'requires_cold_storage' => 'boolean',
        'sort_order' => 'integer',
    ];

    // Status constants
    public const STATUS_PREPARED = 'prepared';
    public const STATUS_LOADED = 'loaded';
    public const STATUS_IN_TRANSIT = 'in_transit';
    public const STATUS_DELIVERED = 'delivered';
    public const STATUS_DAMAGED = 'damaged';
    public const STATUS_RETURNED = 'returned';

    public const STATUSES = [
        self::STATUS_PREPARED => 'Disiapkan',
        self::STATUS_LOADED => 'Dimuat',
        self::STATUS_IN_TRANSIT => 'Dalam Perjalanan',
        self::STATUS_DELIVERED => 'Terkirim',
        self::STATUS_DAMAGED => 'Rusak',
        self::STATUS_RETURNED => 'Dikembalikan',
    ];

    // Condition constants
    public const CONDITION_GOOD = 'baik';
    public const CONDITION_MINOR_DAMAGE = 'rusak_ringan';
    public const CONDITION_MAJOR_DAMAGE = 'rusak_berat';

    public const CONDITIONS = [
        self::CONDITION_GOOD => 'Baik',
        self::CONDITION_MINOR_DAMAGE => 'Rusak Ringan',
        self::CONDITION_MAJOR_DAMAGE => 'Rusak Berat',
    ];

    /**
     * Relationships
     */
    public function deliveryOrder(): BelongsTo
    {
        return $this->belongsTo(DeliveryOrder::class);
    }

    /**
     * Scopes
     */
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    public function scopeFragile($query)
    {
        return $query->where('is_fragile', true);
    }

    public function scopeInsured($query)
    {
        return $query->where('is_insured', true);
    }

    public function scopeWithDiscrepancy($query)
    {
        return $query->whereColumn('actual_quantity', '!=', 'planned_quantity')
            ->orWhere('condition_received', '!=', 'condition_sent');
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }

    /**
     * Model Events
     */
    protected static function booted(): void
    {
        static::creating(function (Item $item) {
            if (is_null($item->sort_order)) {
                $maxOrder = static::where('delivery_order_id', $item->delivery_order_id)
                    ->max('sort_order') ?? 0;
                $item->sort_order = $maxOrder + 1;
            }

            // Auto calculate total value
            if ($item->unit_value && $item->planned_quantity) {
                $item->total_value = $item->unit_value * $item->planned_quantity;
            }
        });

        static::updating(function (Item $item) {
            // Recalculate total value if unit_value or quantity changed
            if ($item->isDirty(['unit_value', 'planned_quantity']) && $item->unit_value && $item->planned_quantity) {
                $item->total_value = $item->unit_value * $item->planned_quantity;
            }
        });

        static::updated(function (Item $item) {
            // Record status history if item status changed
            if ($item->wasChanged('status')) {
                $item->deliveryOrder->recordStatusHistory(
                    'item_status_changed',
                    "Status item '{$item->name}' diubah ke {$item->status_label}"
                );
            }

            // Record discrepancy if actual quantity differs from planned
            if ($item->wasChanged('actual_quantity') && $item->actual_quantity != $item->planned_quantity) {
                $difference = $item->actual_quantity - $item->planned_quantity;
                $type = $difference > 0 ? 'kelebihan' : 'kekurangan';
                $amount = abs($difference);

                $item->deliveryOrder->recordStatusHistory(
                    'discrepancy_noted',
                    "Ketidaksesuaian item '{$item->name}': {$type} {$amount} {$item->unit}"
                );

                // Update delivery order discrepancy flag
                $item->deliveryOrder->update(['has_discrepancy' => true]);
            }
        });
    }

    /**
     * Business Logic Methods
     */
    public function markAsLoaded(): void
    {
        $this->update(['status' => self::STATUS_LOADED]);
    }

    public function markAsInTransit(): void
    {
        $this->update(['status' => self::STATUS_IN_TRANSIT]);
    }

    public function markAsDelivered(?float $actualQuantity = null, ?string $conditionReceived = null): void
    {
        $updateData = ['status' => self::STATUS_DELIVERED];

        if (!is_null($actualQuantity)) {
            $updateData['actual_quantity'] = $actualQuantity;
        }

        if ($conditionReceived) {
            $updateData['condition_received'] = $conditionReceived;
        }

        $this->update($updateData);
    }

    public function markAsDamaged(string $damageNotes = '', ?string $conditionReceived = null): void
    {
        $this->update([
            'status' => self::STATUS_DAMAGED,
            'condition_received' => $conditionReceived ?? self::CONDITION_MAJOR_DAMAGE,
            'damage_notes' => $damageNotes,
        ]);
    }

    public function hasDiscrepancy(): bool
    {
        return $this->actual_quantity != $this->planned_quantity ||
            ($this->condition_received && $this->condition_received != $this->condition_sent);
    }

    public function getDiscrepancyDescription(): ?string
    {
        $discrepancies = [];

        // Quantity discrepancy
        if ($this->actual_quantity && $this->actual_quantity != $this->planned_quantity) {
            $difference = $this->actual_quantity - $this->planned_quantity;
            $type = $difference > 0 ? 'Kelebihan' : 'Kekurangan';
            $amount = abs($difference);
            $discrepancies[] = "{$type}: {$amount} {$this->unit}";
        }

        // Condition discrepancy
        if ($this->condition_received && $this->condition_received != $this->condition_sent) {
            $discrepancies[] = "Kondisi berubah dari {$this->condition_sent_label} ke {$this->condition_received_label}";
        }

        return !empty($discrepancies) ? implode(', ', $discrepancies) : null;
    }

    /**
     * Laravel 12.x Accessor Methods
     */
    protected function statusLabel(): Attribute
    {
        return Attribute::make(
            get: fn() => self::STATUSES[$this->status] ?? $this->status,
        );
    }

    protected function conditionSentLabel(): Attribute
    {
        return Attribute::make(
            get: fn() => self::CONDITIONS[$this->condition_sent] ?? $this->condition_sent,
        );
    }

    protected function conditionReceivedLabel(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->condition_received ? (self::CONDITIONS[$this->condition_received] ?? $this->condition_received) : null,
        );
    }

    protected function formattedWeight(): Attribute
    {
        return Attribute::make(
            get: function () {
                if (!$this->weight) return '-';

                $totalWeight = $this->weight * $this->planned_quantity;
                return number_format($this->weight, 1) . ' kg/unit (' . number_format($totalWeight, 1) . ' kg total)';
            }
        );
    }

    protected function formattedDimensions(): Attribute
    {
        return Attribute::make(
            get: function () {
                if (!$this->length || !$this->width || !$this->height) return '-';

                return "{$this->length} × {$this->width} × {$this->height} cm";
            }
        );
    }

    protected function formattedValue(): Attribute
    {
        return Attribute::make(
            get: function () {
                if (!$this->total_value) return '-';

                return 'Rp ' . number_format($this->total_value, 0, ',', '.');
            }
        );
    }

    protected function volume(): Attribute
    {
        return Attribute::make(
            get: function () {
                if (!$this->length || !$this->width || !$this->height) return null;

                return ($this->length * $this->width * $this->height) / 1000000; // Convert to m³
            }
        );
    }

    protected function formattedVolume(): Attribute
    {
        return Attribute::make(
            get: function () {
                $volume = $this->volume;
                if (!$volume) return '-';

                return number_format($volume, 3) . ' m³';
            }
        );
    }

    protected function specialHandlingRequirements(): Attribute
    {
        return Attribute::make(
            get: function () {
                $requirements = [];

                if ($this->is_fragile) {
                    $requirements[] = 'Barang Mudah Pecah';
                }

                if ($this->requires_cold_storage) {
                    $requirements[] = 'Perlu Penyimpanan Dingin';
                }

                if ($this->is_insured) {
                    $requirements[] = 'Diasuransikan';
                }

                return $requirements;
            }
        );
    }
}
