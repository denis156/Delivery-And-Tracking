<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

class StatusHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'delivery_order_id',
        'user_id',
        'user_role',
        'status_from',
        'status_to',
        'action_type',
        'description',
        'changes',
        'notes',
        'latitude',
        'longitude',
        'location_address',
        'ip_address',
        'user_agent',
        'device_type',
        'reference_id',
        'reference_type',
        'requires_notification',
        'is_critical',
        'is_visible_to_client',
        'occurred_at',
    ];

    protected $casts = [
        'changes' => 'array',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'requires_notification' => 'boolean',
        'is_critical' => 'boolean',
        'is_visible_to_client' => 'boolean',
        'occurred_at' => 'datetime',
    ];

    // Action type constants
    public const ACTION_CREATED = 'created';
    public const ACTION_UPDATED = 'updated';
    public const ACTION_VERIFIED = 'verified';
    public const ACTION_ASSIGNED = 'assigned';
    public const ACTION_DISPATCHED = 'dispatched';
    public const ACTION_LOCATION_UPDATED = 'location_updated';
    public const ACTION_MILESTONE_REACHED = 'milestone_reached';
    public const ACTION_ARRIVED = 'arrived';
    public const ACTION_COMPLETED = 'completed';
    public const ACTION_CANCELLED = 'cancelled';
    public const ACTION_RETURNED = 'returned';
    public const ACTION_DISCREPANCY_NOTED = 'discrepancy_noted';
    public const ACTION_DOCUMENT_PRINTED = 'document_printed';
    public const ACTION_NOTE_ADDED = 'note_added';
    public const ACTION_ITEM_STATUS_CHANGED = 'item_status_changed';
    public const ACTION_DRIVER_REASSIGNED = 'driver_reassigned';

    public const ACTION_TYPES = [
        self::ACTION_CREATED => 'Dibuat',
        self::ACTION_UPDATED => 'Diperbarui',
        self::ACTION_VERIFIED => 'Diverifikasi',
        self::ACTION_ASSIGNED => 'Ditugaskan',
        self::ACTION_DISPATCHED => 'Dikirim',
        self::ACTION_LOCATION_UPDATED => 'Lokasi Diperbarui',
        self::ACTION_MILESTONE_REACHED => 'Milestone Tercapai',
        self::ACTION_ARRIVED => 'Sampai Tujuan',
        self::ACTION_COMPLETED => 'Diselesaikan',
        self::ACTION_CANCELLED => 'Dibatalkan',
        self::ACTION_RETURNED => 'Dikembalikan',
        self::ACTION_DISCREPANCY_NOTED => 'Ketidaksesuaian Dicatat',
        self::ACTION_DOCUMENT_PRINTED => 'Dokumen Dicetak',
        self::ACTION_NOTE_ADDED => 'Catatan Ditambahkan',
        self::ACTION_ITEM_STATUS_CHANGED => 'Status Item Diubah',
        self::ACTION_DRIVER_REASSIGNED => 'Driver Ditugaskan Ulang',
    ];

    // Device type constants
    public const DEVICE_DESKTOP = 'desktop';
    public const DEVICE_MOBILE = 'mobile';
    public const DEVICE_TABLET = 'tablet';
    public const DEVICE_UNKNOWN = 'unknown';

    public const DEVICE_TYPES = [
        self::DEVICE_DESKTOP => 'Desktop',
        self::DEVICE_MOBILE => 'Mobile',
        self::DEVICE_TABLET => 'Tablet',
        self::DEVICE_UNKNOWN => 'Tidak Diketahui',
    ];

    /**
     * Relationships
     */
    public function deliveryOrder(): BelongsTo
    {
        return $this->belongsTo(DeliveryOrder::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scopes
     */
    public function scopeByDeliveryOrder($query, int $deliveryOrderId)
    {
        return $query->where('delivery_order_id', $deliveryOrderId);
    }

    public function scopeByUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeByActionType($query, string $actionType)
    {
        return $query->where('action_type', $actionType);
    }

    public function scopeByRole($query, string $role)
    {
        return $query->where('user_role', $role);
    }

    public function scopeCritical($query)
    {
        return $query->where('is_critical', true);
    }

    public function scopeVisibleToClient($query)
    {
        return $query->where('is_visible_to_client', true);
    }

    public function scopeRequiresNotification($query)
    {
        return $query->where('requires_notification', true);
    }

    public function scopeRecent($query, int $hours = 24)
    {
        return $query->where('occurred_at', '>=', now()->subHours($hours));
    }

    public function scopeToday($query)
    {
        return $query->whereDate('occurred_at', now()->toDateString());
    }

    public function scopeThisWeek($query)
    {
        return $query->whereBetween('occurred_at', [
            now()->startOfWeek(),
            now()->endOfWeek()
        ]);
    }

    public function scopeInTimeRange($query, Carbon $from, Carbon $to)
    {
        return $query->whereBetween('occurred_at', [$from, $to]);
    }

    public function scopeOrderedByTime($query, string $direction = 'desc')
    {
        return $query->orderBy('occurred_at', $direction);
    }

    public function scopeWithLocation($query)
    {
        return $query->whereNotNull('latitude')->whereNotNull('longitude');
    }

    public function scopeByActionTypes($query, array $actionTypes)
    {
        return $query->whereIn('action_type', $actionTypes);
    }

    public function scopeSystemActions($query)
    {
        $systemActions = [
            self::ACTION_CREATED,
            self::ACTION_VERIFIED,
            self::ACTION_DISPATCHED,
            self::ACTION_ARRIVED,
            self::ACTION_COMPLETED,
        ];

        return $query->whereIn('action_type', $systemActions);
    }

    public function scopeUserActions($query)
    {
        $userActions = [
            self::ACTION_NOTE_ADDED,
            self::ACTION_UPDATED,
            self::ACTION_CANCELLED,
            self::ACTION_DOCUMENT_PRINTED,
        ];

        return $query->whereIn('action_type', $userActions);
    }

    /**
     * Model Events
     */
    protected static function booted(): void
    {
        static::creating(function (StatusHistory $history) {
            // Set default occurred_at if not provided
            if (!$history->occurred_at) {
                $history->occurred_at = now();
            }

            // Auto-detect device info if not provided
            if (request()) {
                $history->ip_address = $history->ip_address ?? request()->ip();
                $history->user_agent = $history->user_agent ?? request()->userAgent();
                $history->device_type = $history->device_type ?? $history->detectDeviceType();
            }

            // Set critical flag for important actions
            $criticalActions = [
                self::ACTION_VERIFIED,
                self::ACTION_DISPATCHED,
                self::ACTION_ARRIVED,
                self::ACTION_COMPLETED,
                self::ACTION_CANCELLED,
                self::ACTION_DISCREPANCY_NOTED,
                self::ACTION_DRIVER_REASSIGNED,
            ];

            if (in_array($history->action_type, $criticalActions)) {
                $history->is_critical = true;
                $history->requires_notification = true;
            }

            // Set visibility for client
            $clientVisibleActions = [
                self::ACTION_CREATED,
                self::ACTION_VERIFIED,
                self::ACTION_DISPATCHED,
                self::ACTION_LOCATION_UPDATED,
                self::ACTION_MILESTONE_REACHED,
                self::ACTION_ARRIVED,
                self::ACTION_COMPLETED,
                self::ACTION_CANCELLED,
            ];

            $history->is_visible_to_client = in_array($history->action_type, $clientVisibleActions);
        });
    }

    /**
     * Business Logic Methods
     */
    public function detectDeviceType(): string
    {
        if (!request()) {
            return self::DEVICE_UNKNOWN;
        }

        $userAgent = request()->userAgent();

        if (preg_match('/Mobile|Android|iPhone/', $userAgent)) {
            return self::DEVICE_MOBILE;
        }

        if (preg_match('/iPad|Tablet/', $userAgent)) {
            return self::DEVICE_TABLET;
        }

        if (preg_match('/Windows|Mac|Linux/', $userAgent)) {
            return self::DEVICE_DESKTOP;
        }

        return self::DEVICE_UNKNOWN;
    }

    public function shouldNotify(): bool
    {
        return $this->requires_notification && $this->is_critical;
    }

    public function getNotificationMessage(): string
    {
        $order = $this->deliveryOrder;
        $user = $this->user;

        return match($this->action_type) {
            self::ACTION_CREATED => "Order baru {$order->order_number} dibuat oleh {$user->name}",
            self::ACTION_VERIFIED => "Order {$order->order_number} telah diverifikasi",
            self::ACTION_DISPATCHED => "Driver {$order->driver->name} mulai pengiriman {$order->order_number}",
            self::ACTION_ARRIVED => "Order {$order->order_number} telah sampai di tujuan",
            self::ACTION_COMPLETED => "Order {$order->order_number} telah diselesaikan",
            self::ACTION_CANCELLED => "Order {$order->order_number} dibatalkan",
            self::ACTION_DISCREPANCY_NOTED => "Ketidaksesuaian ditemukan pada {$order->order_number}",
            self::ACTION_DRIVER_REASSIGNED => "Driver untuk {$order->order_number} telah diubah",
            default => $this->description,
        };
    }

    public function getClientMessage(): string
    {
        return match($this->action_type) {
            self::ACTION_CREATED => "Pesanan Anda sedang diproses",
            self::ACTION_VERIFIED => "Pesanan Anda telah diverifikasi dan siap dikirim",
            self::ACTION_DISPATCHED => "Driver sedang dalam perjalanan ke lokasi Anda",
            self::ACTION_LOCATION_UPDATED => "Driver sedang dalam perjalanan",
            self::ACTION_MILESTONE_REACHED => "Driver mencapai checkpoint",
            self::ACTION_ARRIVED => "Driver telah sampai di lokasi Anda",
            self::ACTION_COMPLETED => "Pesanan Anda telah berhasil diselesaikan",
            self::ACTION_CANCELLED => "Pesanan Anda telah dibatalkan",
            default => $this->description,
        };
    }

    public function isSystemGenerated(): bool
    {
        $systemActions = [
            self::ACTION_CREATED,
            self::ACTION_LOCATION_UPDATED,
            self::ACTION_MILESTONE_REACHED,
            self::ACTION_ITEM_STATUS_CHANGED,
        ];

        return in_array($this->action_type, $systemActions);
    }

    public function isUserAction(): bool
    {
        return !$this->isSystemGenerated();
    }

    public function hasLocationData(): bool
    {
        return !is_null($this->latitude) && !is_null($this->longitude);
    }

    public function hasChangesData(): bool
    {
        return !empty($this->changes) && is_array($this->changes);
    }

    /**
     * Laravel 12.x Accessor Methods
     */
    protected function actionTypeLabel(): Attribute
    {
        return Attribute::make(
            get: fn () => self::ACTION_TYPES[$this->action_type] ?? ucfirst(str_replace('_', ' ', $this->action_type)),
        );
    }

    protected function deviceTypeLabel(): Attribute
    {
        return Attribute::make(
            get: fn () => self::DEVICE_TYPES[$this->device_type] ?? ucfirst($this->device_type),
        );
    }

    protected function timeFromNow(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->occurred_at->diffForHumans(),
        );
    }

    protected function formattedTime(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->occurred_at->format('d/m/Y H:i:s'),
        );
    }

    protected function formattedDate(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->occurred_at->format('d/m/Y'),
        );
    }

    protected function formattedTimeOnly(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->occurred_at->format('H:i:s'),
        );
    }

    protected function locationInfo(): Attribute
    {
        return Attribute::make(
            get: function () {
                if (!$this->latitude || !$this->longitude) {
                    return $this->location_address ?: '-';
                }

                $coords = number_format($this->latitude, 6) . ', ' . number_format($this->longitude, 6);

                return $this->location_address ? "{$this->location_address} ({$coords})" : $coords;
            }
        );
    }

    protected function deviceInfo(): Attribute
    {
        return Attribute::make(
            get: function () {
                $info = [];

                if ($this->device_type) {
                    $info[] = $this->device_type_label;
                }

                if ($this->ip_address) {
                    $info[] = "IP: {$this->ip_address}";
                }

                return !empty($info) ? implode(' | ', $info) : '-';
            }
        );
    }

    protected function changesDescription(): Attribute
    {
        return Attribute::make(
            get: function () {
                if (!$this->hasChangesData()) {
                    return null;
                }

                $descriptions = [];

                foreach ($this->changes as $field => $change) {
                    if (isset($change['old'], $change['new'])) {
                        $old = $change['old'] ?? 'kosong';
                        $new = $change['new'] ?? 'kosong';
                        $fieldLabel = ucfirst(str_replace('_', ' ', $field));
                        $descriptions[] = "{$fieldLabel}: '{$old}' → '{$new}'";
                    }
                }

                return !empty($descriptions) ? implode(', ', $descriptions) : null;
            }
        );
    }

    protected function statusChange(): Attribute
    {
        return Attribute::make(
            get: function () {
                if (!$this->status_from || !$this->status_to) {
                    return null;
                }

                $statuses = DeliveryOrder::STATUSES;
                $from = $statuses[$this->status_from] ?? ucfirst(str_replace('_', ' ', $this->status_from));
                $to = $statuses[$this->status_to] ?? ucfirst(str_replace('_', ' ', $this->status_to));

                return "{$from} → {$to}";
            }
        );
    }

    protected function icon(): Attribute
    {
        return Attribute::make(
            get: fn () => match($this->action_type) {
                self::ACTION_CREATED => 'plus-circle',
                self::ACTION_UPDATED => 'pencil-square',
                self::ACTION_VERIFIED => 'check-circle',
                self::ACTION_ASSIGNED => 'user-plus',
                self::ACTION_DISPATCHED => 'truck',
                self::ACTION_LOCATION_UPDATED => 'map-pin',
                self::ACTION_MILESTONE_REACHED => 'flag',
                self::ACTION_ARRIVED => 'map-pin-check',
                self::ACTION_COMPLETED => 'check-circle-2',
                self::ACTION_CANCELLED => 'x-circle',
                self::ACTION_RETURNED => 'arrow-path',
                self::ACTION_DISCREPANCY_NOTED => 'exclamation-triangle',
                self::ACTION_DOCUMENT_PRINTED => 'printer',
                self::ACTION_NOTE_ADDED => 'chat-bubble-left-ellipsis',
                self::ACTION_ITEM_STATUS_CHANGED => 'squares-plus',
                self::ACTION_DRIVER_REASSIGNED => 'arrow-path-rounded-square',
                default => 'information-circle',
            }
        );
    }

    protected function colorClass(): Attribute
    {
        return Attribute::make(
            get: fn () => match($this->action_type) {
                self::ACTION_CREATED => 'text-blue-600',
                self::ACTION_UPDATED => 'text-gray-600',
                self::ACTION_VERIFIED => 'text-green-600',
                self::ACTION_ASSIGNED => 'text-purple-600',
                self::ACTION_DISPATCHED => 'text-orange-600',
                self::ACTION_LOCATION_UPDATED => 'text-blue-500',
                self::ACTION_MILESTONE_REACHED => 'text-indigo-600',
                self::ACTION_ARRIVED => 'text-teal-600',
                self::ACTION_COMPLETED => 'text-green-700',
                self::ACTION_CANCELLED => 'text-red-600',
                self::ACTION_RETURNED => 'text-yellow-600',
                self::ACTION_DISCREPANCY_NOTED => 'text-red-500',
                self::ACTION_DOCUMENT_PRINTED => 'text-gray-700',
                self::ACTION_NOTE_ADDED => 'text-blue-400',
                self::ACTION_ITEM_STATUS_CHANGED => 'text-amber-600',
                self::ACTION_DRIVER_REASSIGNED => 'text-violet-600',
                default => 'text-gray-500',
            }
        );
    }

    protected function badgeClass(): Attribute
    {
        return Attribute::make(
            get: fn () => match($this->action_type) {
                self::ACTION_CREATED => 'badge-info',
                self::ACTION_UPDATED => 'badge-neutral',
                self::ACTION_VERIFIED => 'badge-success',
                self::ACTION_ASSIGNED => 'badge-secondary',
                self::ACTION_DISPATCHED => 'badge-warning',
                self::ACTION_LOCATION_UPDATED => 'badge-info',
                self::ACTION_MILESTONE_REACHED => 'badge-primary',
                self::ACTION_ARRIVED => 'badge-accent',
                self::ACTION_COMPLETED => 'badge-success',
                self::ACTION_CANCELLED => 'badge-error',
                self::ACTION_RETURNED => 'badge-warning',
                self::ACTION_DISCREPANCY_NOTED => 'badge-error',
                self::ACTION_DOCUMENT_PRINTED => 'badge-neutral',
                self::ACTION_NOTE_ADDED => 'badge-info',
                self::ACTION_ITEM_STATUS_CHANGED => 'badge-warning',
                self::ACTION_DRIVER_REASSIGNED => 'badge-secondary',
                default => 'badge-ghost',
            }
        );
    }

    protected function isRecent(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->occurred_at->diffInHours(now()) <= 24,
        );
    }

    protected function isToday(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->occurred_at->isToday(),
        );
    }

    protected function referenceInfo(): Attribute
    {
        return Attribute::make(
            get: function () {
                if (!$this->reference_id || !$this->reference_type) {
                    return null;
                }

                $typeLabel = ucfirst(str_replace('_', ' ', $this->reference_type));
                return "{$typeLabel} ID: {$this->reference_id}";
            }
        );
    }

    protected function userInfo(): Attribute
    {
        return Attribute::make(
            get: function () {
                if (!$this->user) {
                    return 'Sistem';
                }

                $roleLabel = $this->user->role_label ?? ucfirst($this->user_role);
                return "{$this->user->name} ({$roleLabel})";
            }
        );
    }

    protected function shortDescription(): Attribute
    {
        return Attribute::make(
            get: function () {
                if (strlen($this->description) <= 50) {
                    return $this->description;
                }

                return substr($this->description, 0, 47) . '...';
            }
        );
    }
}
