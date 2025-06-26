<?php

namespace App\Models;

use App\Class\Helper\UserHelper;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasRoles, HasFactory, Notifiable, SoftDeletes;

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
        return $query->whereDoesntHave('roles', function ($query) {
            $query->where('name', UserHelper::ROLE_DRIVER);
        });
    }

    public function scopeOnlyDrivers($query)
    {
        return $query->whereHas('roles', function ($query) {
            $query->where('name', UserHelper::ROLE_DRIVER);
        });
    }

    // * ========================================
    // * ACCESSORS (Laravel 12.x Attribute Style) - DIPERBAIKI
    // * ========================================

    /**
     * DIPERBAIKI: Hapus double processing, langsung return asset URL
     */
    protected function avatar(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->avatar_url ? asset('storage/' . $this->avatar_url) : null,
        );
    }

    protected function statusColor(): Attribute
    {
        return Attribute::make(
            get: fn () => UserHelper::getStatusColor($this->is_active),
        );
    }

    protected function statusLabel(): Attribute
    {
        return Attribute::make(
            get: fn () => UserHelper::getStatusLabel($this->is_active),
        );
    }

    /**
     * DIPERBAIKI: Tambah null check yang robust
     */
    protected function roleColor(): Attribute
    {
        return Attribute::make(
            get: function () {
                $role = $this->roles->first();
                return $role ? UserHelper::getRoleColor($role->name) : 'neutral';
            }
        );
    }

    /**
     * DIPERBAIKI: Tambah null check yang robust
     */
    protected function roleLabel(): Attribute
    {
        return Attribute::make(
            get: function () {
                $role = $this->roles->first();
                return $role ? UserHelper::getRoleLabel($role->name) : 'Tidak ada role';
            }
        );
    }

    /**
     * DIPERBAIKI: Tambah null check yang robust
     */
    protected function roleIcon(): Attribute
    {
        return Attribute::make(
            get: function () {
                $role = $this->roles->first();
                return $role ? UserHelper::getRoleIcon($role->name) : 'phosphor.user';
            }
        );
    }

    protected function avatarPlaceholder(): Attribute
    {
        return Attribute::make(
            get: fn () => UserHelper::generateAvatarPlaceholder($this->name),
        );
    }

    /**
     * DIPERBAIKI: Tambah null check yang robust
     */
    protected function displayName(): Attribute
    {
        return Attribute::make(
            get: function () {
                $role = $this->roles->first();
                return $role ? UserHelper::generateDisplayName($this->name, $role->name) : $this->name;
            }
        );
    }

    /**
     * DIPERBAIKI: Tambah null check yang robust
     */
    protected function roleDescription(): Attribute
    {
        return Attribute::make(
            get: function () {
                $role = $this->roles->first();
                return $role ? UserHelper::getRoleDescription($role->name) : 'Belum memiliki role';
            }
        );
    }

    /**
     * DIPERBAIKI: Tambah null check yang robust
     */
    protected function rolePriority(): Attribute
    {
        return Attribute::make(
            get: function () {
                $role = $this->roles->first();
                return $role ? UserHelper::getRolePriority($role->name) : 99;
            }
        );
    }

    // * ========================================
    // * STATIC HELPER METHODS
    // * ========================================

    public static function getAllRoles(): array
    {
        return UserHelper::getAllRoles();
    }

    public static function getRolesExcludeDrivers(): array
    {
        return UserHelper::getRolesExcludeDrivers();
    }

    public static function getManagementRoles(): array
    {
        return UserHelper::getManagementRoles();
    }

    public static function getStaffRoles(): array
    {
        return UserHelper::getStaffRoles();
    }

    public static function getRoleColorByKey(string $role): string
    {
        return UserHelper::getRoleColor($role);
    }

    public static function getRoleLabelByKey(string $role): string
    {
        return UserHelper::getRoleLabel($role);
    }

    public static function getRoleIconByKey(string $role): string
    {
        return UserHelper::getRoleIcon($role);
    }

    public static function isValidRole(string $role): bool
    {
        return UserHelper::isValidRole($role);
    }

    public static function sortByRolePriority(array $users): array
    {
        return UserHelper::sortByRolePriority($users);
    }

    // * ========================================
    // * HELPER METHODS - DIPERBAIKI
    // * ========================================

    public function activate(): bool
    {
        return $this->update(['is_active' => true]);
    }

    public function deactivate(): bool
    {
        return $this->update(['is_active' => false]);
    }

    public function toggleStatus(): bool
    {
        return $this->update(['is_active' => !$this->is_active]);
    }

    public function isActive(): bool
    {
        return $this->is_active;
    }

    public function isInactive(): bool
    {
        return !$this->is_active;
    }

    public function isDriver(): bool
    {
        return $this->hasRole(UserHelper::ROLE_DRIVER);
    }

    public function isManageable(): bool
    {
        return !$this->isDriver();
    }

    public function isAdmin(): bool
    {
        return $this->hasRole(UserHelper::ROLE_ADMIN);
    }

    public function isManager(): bool
    {
        return $this->hasRole(UserHelper::ROLE_MANAGER);
    }

    public function isClient(): bool
    {
        return $this->hasRole(UserHelper::ROLE_CLIENT);
    }

    public function hasManagementAccess(): bool
    {
        return $this->hasAnyRole([UserHelper::ROLE_ADMIN, UserHelper::ROLE_MANAGER]);
    }

    /**
     * DIPERBAIKI: Tambah null check
     */
    public function getPrimaryRole(): ?string
    {
        return $this->roles->first()?->name;
    }

    /**
     * DIPERBAIKI: Method baru untuk cek apakah user punya role
     */
    public function hasAnyRole(): bool
    {
        return $this->roles->isNotEmpty();
    }

    /**
     * DIPERBAIKI: Method yang lebih robust untuk permission checking
     */
    public function canAccessAdmin(): bool
    {
        $role = $this->getPrimaryRole();
        return $role ? UserHelper::canAccessAdmin($role) : false;
    }

    public function canManageUsers(): bool
    {
        $role = $this->getPrimaryRole();
        return $role ? UserHelper::canManageUsers($role) : false;
    }

    public function canManageDeliveries(): bool
    {
        $role = $this->getPrimaryRole();
        return $role ? UserHelper::canManageDeliveries($role) : false;
    }

    public function getAllowedRoleTransitions(): array
    {
        $role = $this->getPrimaryRole();
        return $role ? UserHelper::getAllowedRoleTransitions($role) : [];
    }

    public function getRoleNames(): array
    {
        return $this->roles->pluck('name')->toArray();
    }

    public function setRole(string $role): void
    {
        $this->syncRoles($role);
    }

    public function getRoleAttribute(): ?string
    {
        return $this->getPrimaryRole();
    }
}
