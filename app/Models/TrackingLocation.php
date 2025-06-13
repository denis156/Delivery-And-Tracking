<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

class TrackingLocation extends Model
{
    use HasFactory;

    protected $fillable = [
        'delivery_order_id',
        'driver_id',
        'latitude',
        'longitude',
        'accuracy',
        'altitude',
        'speed',
        'heading',
        'address',
        'city',
        'district',
        'province',
        'location_type',
        'is_milestone',
        'notes',
        'distance_from_start',
        'distance_to_destination',
        'device_info',
        'ip_address',
        'source',
        'battery_level',
        'signal_strength',
        'recorded_at',
    ];

    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'accuracy' => 'decimal:2',
        'altitude' => 'decimal:2',
        'speed' => 'decimal:2',
        'heading' => 'integer',
        'distance_from_start' => 'decimal:2',
        'distance_to_destination' => 'decimal:2',
        'is_milestone' => 'boolean',
        'battery_level' => 'integer',
        'signal_strength' => 'integer',
        'recorded_at' => 'datetime',
    ];

    // Location type constants
    public const TYPE_START = 'start';
    public const TYPE_CHECKPOINT = 'checkpoint';
    public const TYPE_STOP = 'stop';
    public const TYPE_DESTINATION = 'destination';
    public const TYPE_WAYPOINT = 'waypoint';

    public const LOCATION_TYPES = [
        self::TYPE_START => 'Titik Mulai',
        self::TYPE_CHECKPOINT => 'Checkpoint',
        self::TYPE_STOP => 'Berhenti',
        self::TYPE_DESTINATION => 'Tujuan',
        self::TYPE_WAYPOINT => 'Waypoint',
    ];

    // Source constants
    public const SOURCE_WEB_GPS = 'web_gps';
    public const SOURCE_MANUAL = 'manual';
    public const SOURCE_API = 'api';

    public const SOURCES = [
        self::SOURCE_WEB_GPS => 'GPS Browser',
        self::SOURCE_MANUAL => 'Input Manual',
        self::SOURCE_API => 'API Eksternal',
    ];

    /**
     * Relationships
     */
    public function deliveryOrder(): BelongsTo
    {
        return $this->belongsTo(DeliveryOrder::class);
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'driver_id');
    }

    /**
     * Scopes
     */
    public function scopeByDeliveryOrder($query, int $deliveryOrderId)
    {
        return $query->where('delivery_order_id', $deliveryOrderId);
    }

    public function scopeByDriver($query, int $driverId)
    {
        return $query->where('driver_id', $driverId);
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('location_type', $type);
    }

    public function scopeMilestones($query)
    {
        return $query->where('is_milestone', true);
    }

    public function scopeRecent($query, int $hours = 24)
    {
        return $query->where('recorded_at', '>=', now()->subHours($hours));
    }

    public function scopeInTimeRange($query, Carbon $from, Carbon $to)
    {
        return $query->whereBetween('recorded_at', [$from, $to]);
    }

    public function scopeOrderedByTime($query, string $direction = 'asc')
    {
        return $query->orderBy('recorded_at', $direction);
    }

    public function scopeWithinRadius($query, float $centerLat, float $centerLng, float $radiusKm)
    {
        $earthRadius = 6371; // Earth's radius in kilometers

        return $query->whereRaw(
            "({$earthRadius} * acos(cos(radians(?)) * cos(radians(latitude)) * cos(radians(longitude) - radians(?)) + sin(radians(?)) * sin(radians(latitude)))) <= ?",
            [$centerLat, $centerLng, $centerLat, $radiusKm]
        );
    }

    /**
     * Model Events
     */
    protected static function booted(): void
    {
        static::creating(function (TrackingLocation $location) {
            // Set default recorded_at if not provided
            if (!$location->recorded_at) {
                $location->recorded_at = now();
            }

            // Auto-detect device info if not provided
            if (!$location->device_info && request()) {
                $location->device_info = request()->userAgent();
                $location->ip_address = request()->ip();
            }
        });

        static::created(function (TrackingLocation $location) {
            // Update delivery order status if this is a significant location
            if ($location->location_type === self::TYPE_START) {
                $location->deliveryOrder->markAsInTransit();
            } elseif ($location->location_type === self::TYPE_DESTINATION) {
                $location->deliveryOrder->markAsArrived($location->latitude, $location->longitude);
            }

            // Calculate distances if not provided
            $location->calculateDistances();

            // Record status history for milestone locations
            if ($location->is_milestone) {
                $location->deliveryOrder->recordStatusHistory(
                    'location_updated',
                    "Lokasi milestone: {$location->location_type_label}" .
                    ($location->address ? " di {$location->address}" : ""),
                    $location->driver
                );
            }
        });
    }

    /**
     * Business Logic Methods
     */
    public function calculateDistances(): void
    {
        $deliveryOrder = $this->deliveryOrder;

        // Calculate distance from start
        $startLocation = $deliveryOrder->trackingLocations()
                                      ->where('location_type', self::TYPE_START)
                                      ->first();

        if ($startLocation && $startLocation->id !== $this->id) {
            $this->distance_from_start = $this->calculateDistance(
                $startLocation->latitude,
                $startLocation->longitude,
                $this->latitude,
                $this->longitude
            );
        }

        // Calculate distance to destination
        if ($deliveryOrder->destination_latitude && $deliveryOrder->destination_longitude) {
            $this->distance_to_destination = $this->calculateDistance(
                $this->latitude,
                $this->longitude,
                $deliveryOrder->destination_latitude,
                $deliveryOrder->destination_longitude
            );
        }

        // Save if distances were calculated
        if ($this->isDirty(['distance_from_start', 'distance_to_destination'])) {
            $this->saveQuietly();
        }
    }

    public function calculateDistance(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earthRadius = 6371; // kilometers

        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLng / 2) * sin($dLng / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

    public function isStale(int $maxAgeMinutes = 30): bool
    {
        return $this->recorded_at->diffInMinutes(now()) > $maxAgeMinutes;
    }

    public function getNextLocation(): ?TrackingLocation
    {
        return static::where('delivery_order_id', $this->delivery_order_id)
                    ->where('recorded_at', '>', $this->recorded_at)
                    ->orderBy('recorded_at')
                    ->first();
    }

    public function getPreviousLocation(): ?TrackingLocation
    {
        return static::where('delivery_order_id', $this->delivery_order_id)
                    ->where('recorded_at', '<', $this->recorded_at)
                    ->orderBy('recorded_at', 'desc')
                    ->first();
    }

    /**
     * Laravel 12.x Accessor Methods
     */
    protected function locationTypeLabel(): Attribute
    {
        return Attribute::make(
            get: fn () => self::LOCATION_TYPES[$this->location_type] ?? $this->location_type,
        );
    }

    protected function sourceLabel(): Attribute
    {
        return Attribute::make(
            get: fn () => self::SOURCES[$this->source] ?? $this->source,
        );
    }

    protected function coordinates(): Attribute
    {
        return Attribute::make(
            get: fn () => "{$this->latitude}, {$this->longitude}",
        );
    }

    protected function formattedSpeed(): Attribute
    {
        return Attribute::make(
            get: function () {
                if (!$this->speed) return '-';

                return number_format($this->speed, 1) . ' km/jam';
            }
        );
    }

    protected function formattedHeading(): Attribute
    {
        return Attribute::make(
            get: function () {
                if (is_null($this->heading)) return '-';

                $directions = [
                    'Utara', 'Timur Laut', 'Timur', 'Tenggara',
                    'Selatan', 'Barat Daya', 'Barat', 'Barat Laut'
                ];

                $index = round($this->heading / 45) % 8;

                return $directions[$index] . " ({$this->heading}°)";
            }
        );
    }

    protected function formattedAccuracy(): Attribute
    {
        return Attribute::make(
            get: function () {
                if (!$this->accuracy) return '-';

                return number_format($this->accuracy, 1) . ' meter';
            }
        );
    }

    protected function timeFromNow(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->recorded_at->diffForHumans(),
        );
    }

    protected function formattedDistanceFromStart(): Attribute
    {
        return Attribute::make(
            get: function () {
                if (!$this->distance_from_start) return '-';

                return number_format($this->distance_from_start, 1) . ' km dari start';
            }
        );
    }

    protected function formattedDistanceToDestination(): Attribute
    {
        return Attribute::make(
            get: function () {
                if (!$this->distance_to_destination) return '-';

                return number_format($this->distance_to_destination, 1) . ' km ke tujuan';
            }
        );
    }

    protected function batteryStatus(): Attribute
    {
        return Attribute::make(
            get: function () {
                if (!$this->battery_level) return '-';

                $level = $this->battery_level;

                if ($level >= 80) return "Penuh ({$level}%)";
                if ($level >= 50) return "Cukup ({$level}%)";
                if ($level >= 20) return "Rendah ({$level}%)";

                return "Kritis ({$level}%)";
            }
        );
    }

    protected function signalStatus(): Attribute
    {
        return Attribute::make(
            get: function () {
                if (!$this->signal_strength) return '-';

                $strength = $this->signal_strength;

                return match(true) {
                    $strength >= 4 => 'Sangat Kuat',
                    $strength >= 3 => 'Kuat',
                    $strength >= 2 => 'Sedang',
                    $strength >= 1 => 'Lemah',
                    default => 'Tidak Ada Sinyal'
                } . " ({$strength}/5)";
            }
        );
    }

    protected function mapUrl(): Attribute
    {
        return Attribute::make(
            get: fn () => "https://www.google.com/maps?q={$this->latitude},{$this->longitude}",
        );
    }
}
