<?php

namespace App\Models;

use Spatie\Permission\Traits\HasRoles;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasRoles, HasFactory, Notifiable, SoftDeletes;

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

    /**
     * Scope untuk exclude drivers - menggunakan Spatie Permission
     */
    public function scopeExcludeDrivers($query)
    {
        return $query->whereDoesntHave('roles', function ($query) {
            $query->where('name', self::ROLE_DRIVER);
        });
    }

    /**
     * Scope untuk hanya drivers - menggunakan Spatie Permission
     */
    public function scopeOnlyDrivers($query)
    {
        return $query->whereHas('roles', function ($query) {
            $query->where('name', self::ROLE_DRIVER);
        });
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
     * Get role color for badge - menggunakan Spatie role pertama
     */
    protected function roleColor(): Attribute
    {
        return Attribute::make(
            get: function () {
                $role = $this->roles->first();
                return $role ? (self::$roleColors[$role->name] ?? 'neutral') : 'neutral';
            }
        );
    }

    /**
     * Get role label in Indonesian - menggunakan Spatie role pertama
     */
    protected function roleLabel(): Attribute
    {
        return Attribute::make(
            get: function () {
                $role = $this->roles->first();
                return $role ? (self::$roleLabels[$role->name] ?? ucfirst($role->name)) : 'Tidak ada role';
            }
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
    // * HELPER METHODS - Updated untuk Spatie Permission
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
     * Check if user is driver - menggunakan Spatie hasRole()
     */
    public function isDriver(): bool
    {
        return $this->hasRole(self::ROLE_DRIVER);
    }

    /**
     * Check if user can be managed in regular user management (non-driver)
     */
    public function isManageable(): bool
    {
        return !$this->isDriver();
    }

    /**
     * Check if user is admin - menggunakan Spatie hasRole()
     */
    public function isAdmin(): bool
    {
        return $this->hasRole(self::ROLE_ADMIN);
    }

    /**
     * Check if user is manager - menggunakan Spatie hasRole()
     */
    public function isManager(): bool
    {
        return $this->hasRole(self::ROLE_MANAGER);
    }

    /**
     * Check if user is client - menggunakan Spatie hasRole()
     */
    public function isClient(): bool
    {
        return $this->hasRole(self::ROLE_CLIENT);
    }

    /**
     * Check if user has management access (admin or manager)
     */
    public function hasManagementAccess(): bool
    {
        return $this->hasAnyRole([self::ROLE_ADMIN, self::ROLE_MANAGER]);
    }

    /**
     * Get primary role name (first role) - menggunakan Spatie
     */
    public function getPrimaryRole(): ?string
    {
        return $this->roles->first()?->name;
    }

    /**
     * Get all role names as array - menggunakan Spatie
     */
    public function getRoleNames(): array
    {
        return $this->roles->pluck('name')->toArray();
    }

    /**
     * Assign role ke user - wrapper untuk Spatie assignRole
     */
    public function setRole(string $role): void
    {
        $this->syncRoles($role); // Sync menghapus role lama dan assign role baru
    }

    /**
     * Get first role name untuk backward compatibility
     */
    public function getRoleAttribute(): ?string
    {
        return $this->getPrimaryRole();
    }
}
