<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Casts\Attribute;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, SoftDeletes;

    // * ========================================
    // * KONSTANTA PERAN PENGGUNA
    // * ========================================

    const ROLE_MANAGER = 'manager';
    const ROLE_ADMIN = 'admin';
    const ROLE_DRIVER = 'driver';
    const ROLE_CLIENT = 'client';
    const ROLE_PETUGAS_LAPANGAN = 'petugas-lapangan';
    const ROLE_PETUGAS_RUANGAN = 'petugas-ruangan';
    const ROLE_PETUGAS_GUDANG = 'petugas-gudang';

    // * ========================================
    // * KONFIGURASI WARNA PERAN (DaisyUI Colors)
    // * ========================================

    /**
     * Mapping warna badge untuk setiap peran pengguna sesuai DaisyUI documentation
     */
    private static array $roleColors = [
        self::ROLE_MANAGER => 'primary',
        self::ROLE_ADMIN => 'secondary',
        self::ROLE_CLIENT => 'info',
        self::ROLE_DRIVER => 'warning',
        self::ROLE_PETUGAS_LAPANGAN => 'success',
        self::ROLE_PETUGAS_RUANGAN => 'accent',
        self::ROLE_PETUGAS_GUDANG => 'neutral',
    ];

    // * ========================================
    // * LABEL PERAN BAHASA INDONESIA
    // * ========================================

    /**
     * Mapping label peran dalam bahasa Indonesia
     */
    private static array $roleLabels = [
        self::ROLE_ADMIN => 'Administrator',
        self::ROLE_MANAGER => 'Manajer',
        self::ROLE_CLIENT => 'Klien',
        self::ROLE_DRIVER => 'Sopir',
        self::ROLE_PETUGAS_LAPANGAN => 'Petugas Lapangan',
        self::ROLE_PETUGAS_RUANGAN => 'Petugas Ruangan',
        self::ROLE_PETUGAS_GUDANG => 'Petugas Gudang',
    ];

    // * ========================================
    // * KONFIGURASI MODEL
    // * ========================================

    protected $fillable = [
        'name',
        'email',
        'role',
        'is_active',
        'password',
        'avatar_url',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast (Laravel 12.x style)
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }

    // * ========================================
    // * QUERY SCOPES
    // * ========================================

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeInactive($query)
    {
        return $query->where('is_active', false);
    }

    public function scopeExcludeDrivers($query)
    {
        return $query->where('role', '!=', self::ROLE_DRIVER);
    }

    // * ========================================
    // * ACCESSORS (Laravel 12.x Attribute Style)
    // * ========================================

    /**
     * Get avatar URL with fallback - untuk digunakan di seluruh aplikasi
     * Returns full asset URL or null for placeholder
     */
    protected function avatar(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->avatar_url ? asset('storage/' . $this->avatar_url) : null,
        );
    }

    /**
     * Get status color based on is_active state - konsisten di seluruh aplikasi
     * Returns DaisyUI color class sesuai dokumentasi
     */
    protected function statusColor(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->is_active ? 'success' : 'warning',
        );
    }

    /**
     * Get status label in Indonesian
     */
    protected function statusLabel(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->is_active ? 'Aktif' : 'Nonaktif',
        );
    }

    /**
     * Get role color for badge - konsisten di seluruh aplikasi
     */
    protected function roleColor(): Attribute
    {
        return Attribute::make(
            get: fn () => self::$roleColors[$this->role] ?? 'neutral',
        );
    }

    /**
     * Get role label in Indonesian
     */
    protected function roleLabel(): Attribute
    {
        return Attribute::make(
            get: fn () => self::$roleLabels[$this->role] ?? ucfirst($this->role),
        );
    }

    /**
     * Get avatar placeholder (initials)
     */
    protected function avatarPlaceholder(): Attribute
    {
        return Attribute::make(
            get: function () {
                $nameParts = explode(' ', $this->name);
                if (count($nameParts) >= 2) {
                    return strtoupper(substr($nameParts[0], 0, 1) . substr($nameParts[1], 0, 1));
                }
                return strtoupper(substr($this->name, 0, 2));
            }
        );
    }

    // * ========================================
    // * STATIC HELPER METHODS
    // * ========================================

    /**
     * Get all available roles for dropdowns
     */
    public static function getAllRoles(): array
    {
        return self::$roleLabels;
    }

    /**
     * Get all available roles excluding drivers
     */
    public static function getRolesExcludeDrivers(): array
    {
        $roles = self::$roleLabels;
        unset($roles[self::ROLE_DRIVER]);
        return $roles;
    }

    /**
     * Get role color by role key - static method untuk digunakan di luar model
     */
    public static function getRoleColorByKey(string $role): string
    {
        return self::$roleColors[$role] ?? 'neutral';
    }

    /**
     * Get role label by role key - static method untuk digunakan di luar model
     */
    public static function getRoleLabelByKey(string $role): string
    {
        return self::$roleLabels[$role] ?? ucfirst($role);
    }

    // * ========================================
    // * HELPER METHODS
    // * ========================================

    /**
     * Mengaktifkan pengguna
     */
    public function activate(): bool
    {
        return $this->update(['is_active' => true]);
    }

    /**
     * Menonaktifkan pengguna
     */
    public function deactivate(): bool
    {
        return $this->update(['is_active' => false]);
    }

    /**
     * Toggle status aktif pengguna
     */
    public function toggleStatus(): bool
    {
        return $this->update(['is_active' => !$this->is_active]);
    }

    /**
     * Cek apakah pengguna aktif
     */
    public function isActive(): bool
    {
        return $this->is_active;
    }

    /**
     * Cek apakah pengguna nonaktif
     */
    public function isInactive(): bool
    {
        return !$this->is_active;
    }

    /**
     * Check if user is driver
     */
    public function isDriver(): bool
    {
        return $this->role === self::ROLE_DRIVER;
    }

    /**
     * Check if user can be managed in regular user management (non-driver)
     */
    public function isManageable(): bool
    {
        return !$this->isDriver();
    }
}
