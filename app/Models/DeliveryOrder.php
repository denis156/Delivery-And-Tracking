<?php

namespace App\Models;

use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DeliveryOrder extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'order_number',
        'status',
        'sender_name',
        'sender_address',
        'sender_phone',
        'recipient_name',
        'recipient_address',
        'recipient_phone',
        'recipient_pic',
        'destination_latitude',
        'destination_longitude',
        'created_by',
        'verified_by',
        'driver_id',
        'completed_by',
        'verified_at',
        'dispatched_at',
        'arrived_at',
        'completed_at',
        'planned_delivery_date',
        'planned_delivery_time',
        'estimated_distance',
        'actual_distance',
        'notes',
        'delivery_notes',
        'completion_notes',
        'has_discrepancy',
        'discrepancy_notes',
        'physical_document_number',
        'document_printed_at',
    ];

    protected $casts = [
        'verified_at' => 'datetime',
        'dispatched_at' => 'datetime',
        'arrived_at' => 'datetime',
        'completed_at' => 'datetime',
        'document_printed_at' => 'datetime',
        'planned_delivery_date' => 'date',
        'planned_delivery_time' => 'datetime:H:i',
        'destination_latitude' => 'decimal:8',
        'destination_longitude' => 'decimal:8',
        'estimated_distance' => 'decimal:2',
        'actual_distance' => 'decimal:2',
        'has_discrepancy' => 'boolean',
    ];

    // Status constants untuk type safety
    public const STATUS_DRAFT = 'draft';
    public const STATUS_VERIFIED = 'verified';
    public const STATUS_DISPATCHED = 'dispatched';
    public const STATUS_IN_TRANSIT = 'in_transit';
    public const STATUS_ARRIVED = 'arrived';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CANCELLED = 'cancelled';

    public const STATUSES = [
        self::STATUS_DRAFT => 'Draft',
        self::STATUS_VERIFIED => 'Terverifikasi',
        self::STATUS_DISPATCHED => 'Dikirim',
        self::STATUS_IN_TRANSIT => 'Dalam Perjalanan',
        self::STATUS_ARRIVED => 'Sampai Tujuan',
        self::STATUS_COMPLETED => 'Selesai',
        self::STATUS_CANCELLED => 'Dibatalkan',
    ];

    /**
     * Relationships
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'driver_id');
    }

    public function completedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'completed_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(Item::class)->orderBy('sort_order');
    }

    public function trackingLocations(): HasMany
    {
        return $this->hasMany(TrackingLocation::class)->orderBy('recorded_at');
    }

    public function statusHistories(): HasMany
    {
        return $this->hasMany(StatusHistory::class)->orderBy('occurred_at');
    }

    /**
     * Scopes untuk query optimization
     */
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

    public function scopeActiveOrders($query)
    {
        return $query->whereIn('status', [
            self::STATUS_VERIFIED,
            self::STATUS_DISPATCHED,
            self::STATUS_IN_TRANSIT,
            self::STATUS_ARRIVED
        ]);
    }

    public function scopeCompletedOrders($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    public function scopeDeliveryDateRange($query, Carbon $from, Carbon $to)
    {
        return $query->whereBetween('planned_delivery_date', [$from, $to]);
    }

    public function scopeOverdue($query)
    {
        return $query->where('planned_delivery_date', '<', now()->toDateString())
            ->whereNotIn('status', [self::STATUS_COMPLETED, self::STATUS_CANCELLED]);
    }

    public function scopeToday($query)
    {
        return $query->where('planned_delivery_date', now()->toDateString());
    }

    public function scopeThisWeek($query)
    {
        return $query->whereBetween('planned_delivery_date', [
            now()->startOfWeek(),
            now()->endOfWeek()
        ]);
    }

    public function scopeWithDiscrepancy($query)
    {
        return $query->where('has_discrepancy', true);
    }

    /**
     * Model Events
     */
    protected static function booted(): void
    {
        static::creating(function (DeliveryOrder $deliveryOrder) {
            if (empty($deliveryOrder->order_number)) {
                $deliveryOrder->order_number = $deliveryOrder->generateOrderNumber();
            }
        });

        static::created(function (DeliveryOrder $deliveryOrder) {
            $deliveryOrder->recordStatusHistory('created', 'Order delivery dibuat');
        });

        static::updated(function (DeliveryOrder $deliveryOrder) {
            if ($deliveryOrder->wasChanged('status')) {
                $deliveryOrder->recordStatusHistory(
                    'status_changed',
                    "Status diubah dari {$deliveryOrder->getOriginal('status')} ke {$deliveryOrder->status}"
                );
            }

            if ($deliveryOrder->wasChanged('driver_id')) {
                $oldDriver = $deliveryOrder->getOriginal('driver_id');
                $newDriver = $deliveryOrder->driver_id;

                if ($oldDriver && $newDriver) {
                    $deliveryOrder->recordStatusHistory(
                        'driver_reassigned',
                        "Driver ditugaskan ulang"
                    );
                } elseif ($newDriver) {
                    $deliveryOrder->recordStatusHistory(
                        'driver_assigned',
                        "Driver {$deliveryOrder->driver->name} ditugaskan"
                    );
                }
            }
        });
    }

    /**
     * Business Logic Methods
     */
    public function generateOrderNumber(): string
    {
        $prefix = 'DO';
        $date = now()->format('ymd');
        $random = strtoupper(Str::random(4));

        return "{$prefix}{$date}{$random}";
    }

    public function canBeVerified(): bool
    {
        return $this->status === self::STATUS_DRAFT && $this->items()->count() > 0;
    }

    public function canBeDispatched(): bool
    {
        return $this->status === self::STATUS_VERIFIED && !empty($this->driver_id);
    }

    public function canBeCompleted(): bool
    {
        return in_array($this->status, [self::STATUS_ARRIVED, self::STATUS_IN_TRANSIT]);
    }

    public function canBeCancelled(): bool
    {
        return !in_array($this->status, [self::STATUS_COMPLETED, self::STATUS_CANCELLED]);
    }

    public function isInProgress(): bool
    {
        return in_array($this->status, [
            self::STATUS_DISPATCHED,
            self::STATUS_IN_TRANSIT,
            self::STATUS_ARRIVED
        ]);
    }

    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    public function verify(User $verifier): void
    {
        if (!$this->canBeVerified()) {
            throw new \Exception('Delivery order tidak dapat diverifikasi');
        }

        $this->update([
            'status' => self::STATUS_VERIFIED,
            'verified_by' => $verifier->id,
            'verified_at' => now(),
        ]);

        $this->recordStatusHistory('verified', 'Order diverifikasi dan siap dispatch', $verifier);
    }

    public function assignDriver(User $driver): void
    {
        if ($this->status !== self::STATUS_VERIFIED) {
            throw new \Exception('Order harus diverifikasi terlebih dahulu');
        }

        $this->update(['driver_id' => $driver->id]);
        $this->recordStatusHistory('assigned', "Driver {$driver->name} ditugaskan");
    }

    public function dispatch(User $dispatcher): void
    {
        if (!$this->canBeDispatched()) {
            throw new \Exception('Delivery order tidak dapat di-dispatch');
        }

        $this->update([
            'status' => self::STATUS_DISPATCHED,
            'dispatched_at' => now(),
        ]);

        $this->recordStatusHistory('dispatched', 'Driver mulai perjalanan', $dispatcher);
    }

    public function markAsInTransit(?float $latitude = null, ?float $longitude = null): void
    {
        $this->update(['status' => self::STATUS_IN_TRANSIT]);

        if ($latitude && $longitude) {
            $this->trackingLocations()->create([
                'driver_id' => $this->driver_id,
                'latitude' => $latitude,
                'longitude' => $longitude,
                'location_type' => 'start',
                'is_milestone' => true,
            ]);
        }

        $this->recordStatusHistory('in_transit', 'Tracking aktif - dalam perjalanan');
    }

    public function markAsArrived(?float $latitude = null, ?float $longitude = null): void
    {
        $this->update([
            'status' => self::STATUS_ARRIVED,
            'arrived_at' => now(),
        ]);

        if ($latitude && $longitude) {
            $this->trackingLocations()->create([
                'driver_id' => $this->driver_id,
                'latitude' => $latitude,
                'longitude' => $longitude,
                'location_type' => 'destination',
                'is_milestone' => true,
            ]);
        }

        $this->recordStatusHistory('arrived', 'Sampai di tujuan');
    }

    public function complete(User $completedBy, ?string $completionNotes = null): void
    {
        if (!$this->canBeCompleted()) {
            throw new \Exception('Delivery order tidak dapat diselesaikan');
        }

        $this->update([
            'status' => self::STATUS_COMPLETED,
            'completed_by' => $completedBy->id,
            'completed_at' => now(),
            'completion_notes' => $completionNotes,
        ]);

        // Update all items status to delivered
        $this->items()->update(['status' => 'delivered']);

        $this->recordStatusHistory('completed', 'Delivery order diselesaikan', $completedBy);
    }

    public function cancel(User $cancelledBy, string $reason): void
    {
        if (!$this->canBeCancelled()) {
            throw new \Exception('Delivery order tidak dapat dibatalkan');
        }

        $this->update([
            'status' => self::STATUS_CANCELLED,
            'completion_notes' => "Dibatalkan: {$reason}",
        ]);

        $this->recordStatusHistory('cancelled', "Order dibatalkan: {$reason}", $cancelledBy);
    }

    public function recordStatusHistory(string $actionType, string $description, ?User $user = null): void
    {
        $currentUser = $user ?? Auth::user();

        $this->statusHistories()->create([
            'user_id' => $currentUser?->id ?? null,
            'user_role' => $currentUser?->role ?? null,
            'action_type' => $actionType,
            'status_from' => $this->getOriginal('status'),
            'status_to' => $this->status,
            'description' => $description,
            'occurred_at' => now(),
        ]);
    }

    public function updateLocation(float $latitude, float $longitude, ?string $address = null): TrackingLocation
    {
        return $this->trackingLocations()->create([
            'driver_id' => $this->driver_id,
            'latitude' => $latitude,
            'longitude' => $longitude,
            'address' => $address,
            'location_type' => 'waypoint',
            'recorded_at' => now(),
        ]);
    }

    public function printDocument(User $printedBy): void
    {
        $documentNumber = $this->generateDocumentNumber();

        $this->update([
            'physical_document_number' => $documentNumber,
            'document_printed_at' => now(),
        ]);

        $this->recordStatusHistory(
            'document_printed',
            "Dokumen fisik dicetak: {$documentNumber}",
            $printedBy
        );
    }

    private function generateDocumentNumber(): string
    {
        $prefix = 'SJ';
        $date = now()->format('ymd');
        $sequence = str_pad($this->id, 4, '0', STR_PAD_LEFT);

        return "{$prefix}{$date}{$sequence}";
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

    protected function statusColor(): Attribute
    {
        return Attribute::make(
            get: fn() => match ($this->status) {
                self::STATUS_DRAFT => 'gray',
                self::STATUS_VERIFIED => 'blue',
                self::STATUS_DISPATCHED => 'orange',
                self::STATUS_IN_TRANSIT => 'yellow',
                self::STATUS_ARRIVED => 'purple',
                self::STATUS_COMPLETED => 'green',
                self::STATUS_CANCELLED => 'red',
                default => 'gray',
            }
        );
    }

    protected function totalItems(): Attribute
    {
        return Attribute::make(
            get: fn() => (int) $this->items()->sum('planned_quantity'),
        );
    }

    protected function totalWeight(): Attribute
    {
        return Attribute::make(
            get: fn() => (float) $this->items()->sum(DB::raw('planned_quantity * COALESCE(weight, 0)')),
        );
    }

    protected function totalValue(): Attribute
    {
        return Attribute::make(
            get: fn() => (float) ($this->items()->sum('total_value') ?? 0),
        );
    }

    protected function lastTrackingLocation(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->trackingLocations()->latest('recorded_at')->first(),
        );
    }

    protected function estimatedArrival(): Attribute
    {
        return Attribute::make(
            get: function () {
                if (!$this->planned_delivery_date) {
                    return null;
                }

                // Jika planned_delivery_time ada, gabungkan dengan tanggal
                if ($this->planned_delivery_time) {
                    try {
                        // planned_delivery_time sudah berupa Carbon instance dari cast
                        // Jadi kita hanya perlu ambil time component-nya
                        $timeString = $this->planned_delivery_time->format('H:i:s');

                        return Carbon::parse(
                            $this->planned_delivery_date->format('Y-m-d') . ' ' . $timeString
                        );
                    } catch (\Exception $e) {
                        Log::warning('Error parsing estimated_arrival', [
                            'planned_delivery_date' => $this->planned_delivery_date,
                            'planned_delivery_time' => $this->planned_delivery_time,
                            'error' => $e->getMessage()
                        ]);
                        return null;
                    }
                }

                // Jika tidak ada waktu spesifik, default ke jam 17:00
                return Carbon::parse($this->planned_delivery_date->format('Y-m-d') . ' 17:00:00');
            }
        );
    }

    protected function isOverdue(): Attribute
    {
        return Attribute::make(
            get: function () {
                if (!$this->estimated_arrival || $this->status === self::STATUS_COMPLETED) {
                    return false;
                }

                return now()->gt($this->estimated_arrival);
            }
        );
    }

    protected function progressPercentage(): Attribute
    {
        return Attribute::make(
            get: fn() => match ($this->status) {
                self::STATUS_DRAFT => 10,
                self::STATUS_VERIFIED => 25,
                self::STATUS_DISPATCHED => 40,
                self::STATUS_IN_TRANSIT => 70,
                self::STATUS_ARRIVED => 90,
                self::STATUS_COMPLETED => 100,
                self::STATUS_CANCELLED => 0,
                default => 0,
            }
        );
    }

    protected function formattedDistance(): Attribute
    {
        return Attribute::make(
            get: function () {
                if (!$this->actual_distance) {
                    return $this->estimated_distance ? number_format($this->estimated_distance, 1) . ' km (estimasi)' : '-';
                }

                return number_format($this->actual_distance, 1) . ' km';
            }
        );
    }

    protected function formattedValue(): Attribute
    {
        return Attribute::make(
            get: fn() => 'Rp ' . number_format($this->total_value, 0, ',', '.'),
        );
    }

    protected function formattedWeight(): Attribute
    {
        return Attribute::make(
            get: fn() => number_format($this->total_weight, 1) . ' kg',
        );
    }

    protected function duration(): Attribute
    {
        return Attribute::make(
            get: function () {
                if (!$this->dispatched_at) {
                    return null;
                }

                $endTime = $this->completed_at ?? now();
                $duration = $this->dispatched_at->diff($endTime);

                if ($duration->days > 0) {
                    return $duration->days . ' hari ' . $duration->h . ' jam';
                }

                return $duration->h . ' jam ' . $duration->i . ' menit';
            }
        );
    }

    protected function deliveryTimeStatus(): Attribute
    {
        return Attribute::make(
            get: function () {
                if ($this->status === self::STATUS_COMPLETED) {
                    return $this->is_overdue ? 'Terlambat' : 'Tepat Waktu';
                }

                if ($this->is_overdue) {
                    return 'Overdue';
                }

                if ($this->estimated_arrival && now()->diffInHours($this->estimated_arrival) <= 2) {
                    return 'Mendekati Deadline';
                }

                return 'On Time';
            }
        );
    }

    protected function currentLocation(): Attribute
    {
        return Attribute::make(
            get: function () {
                $lastLocation = $this->last_tracking_location;

                if (!$lastLocation) {
                    return null;
                }

                return [
                    'latitude' => $lastLocation->latitude,
                    'longitude' => $lastLocation->longitude,
                    'address' => $lastLocation->address,
                    'recorded_at' => $lastLocation->recorded_at,
                ];
            }
        );
    }

    protected function trackingUrl(): Attribute
    {
        return Attribute::make(
            get: fn() => route('tracking.show', $this->order_number),
        );
    }

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

    /**
     * Utility Methods
     */
    public function hasDiscrepancies(): bool
    {
        return $this->has_discrepancy ||
            $this->items()->whereColumn('actual_quantity', '!=', 'planned_quantity')->exists();
    }
}
