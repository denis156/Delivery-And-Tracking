<?php

namespace App\Livewire\App\Pages\Permission;

use Spatie\Permission\Models\Permission;
use Mary\Traits\Toast;
use App\Class\StatusHelper;
use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;

#[Title('Edit Permission')]
#[Layout('livewire.layouts.app')]
class Edit extends Component
{
    use Toast;

    // * ========================================
    // * PROPERTIES (Livewire 3 Standards)
    // * ========================================

    public Permission $permission;
    public string $name = '';
    public string $guard_name = '';
    public string $originalName = '';
    public string $originalGuardName = '';

    // * ========================================
    // * LIFECYCLE HOOKS
    // * ========================================

    public function mount(Permission $permission): void
    {
        $this->permission = $permission;
        $this->name = $permission->name;
        $this->guard_name = $permission->guard_name;

        // Store original values for comparison
        $this->originalName = $permission->name;
        $this->originalGuardName = $permission->guard_name;
    }

    // * ========================================
    // * VALIDATION RULES
    // * ========================================

    protected function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'min:3',
                'max:255',
                'unique:permissions,name,' . $this->permission->id . ',id,guard_name,' . $this->guard_name,
                'regex:/^[a-z0-9\s\-_\.]+$/i'
            ],
            'guard_name' => ['required', 'string', 'in:web,api'],
        ];
    }

    protected function validationAttributes(): array
    {
        return [
            'name' => 'nama permission',
            'guard_name' => 'guard',
        ];
    }

    protected function messages(): array
    {
        return [
            'name.required' => 'Nama permission wajib diisi.',
            'name.min' => 'Nama permission minimal 3 karakter.',
            'name.max' => 'Nama permission maksimal 255 karakter.',
            'name.unique' => 'Permission dengan nama ini sudah ada untuk guard yang sama.',
            'name.regex' => 'Nama permission hanya boleh berisi huruf, angka, spasi, strip, underscore, dan titik.',

            'guard_name.required' => 'Guard wajib dipilih.',
            'guard_name.in' => 'Guard yang dipilih tidak valid.',
        ];
    }

    // * ========================================
    // * REAL-TIME VALIDATION
    // * ========================================

    public function updatedName(): void
    {
        $this->validateOnly('name');
    }

    public function updatedGuardName(): void
    {
        $this->validateOnly('name'); // Re-validate name because unique rule depends on guard
    }

    // * ========================================
    // * ACTIONS
    // * ========================================

    public function save(): void
    {
        // Check if permission is being used and guard is being changed
        if ($this->guard_name !== $this->originalGuardName && $this->hasUsages()) {
            $this->error(
                'Tidak dapat mengubah guard! Permission sedang digunakan oleh roles atau users.',
                position: 'toast-top toast-end'
            );
            return;
        }

        $this->validate();

        try {
            $this->permission->update([
                'name' => trim($this->name),
                'guard_name' => $this->guard_name,
            ]);

            $this->success(
                "Permission '{$this->permission->name}' berhasil diperbarui!",
                position: 'toast-top toast-end',
                redirectTo: route('app.permission.view', $this->permission)
            );
        } catch (\Exception $e) {
            $this->error(
                'Gagal memperbarui permission! Terjadi kesalahan sistem.',
                position: 'toast-top toast-end'
            );
        }
    }

    public function cancel(): void
    {
        $this->redirect(route('app.permission.view', $this->permission), navigate: true);
    }

    public function resetForm(): void
    {
        $this->name = $this->originalName;
        $this->guard_name = $this->originalGuardName;
        $this->resetValidation();

        $this->info('Form berhasil direset!', position: 'toast-top toast-end');
    }

    public function deletePermission(): void
    {
        if (!StatusHelper::canPermissionBeDeleted($this->permission)) {
            $this->error(
                'Permission masih digunakan oleh roles atau users.',
                position: 'toast-top toast-end'
            );
            return;
        }

        $this->dispatch('openDeletePermissionModal', $this->permission->id);
    }

    public function viewPermission(): void
    {
        $this->redirect(route('app.permission.view', $this->permission), navigate: true);
    }

    // * ========================================
    // * HELPER METHODS USING STATUSHELPER
    // * ========================================

    public function getPermissionCategory(string $name): string
    {
        return StatusHelper::getPermissionCategory($name);
    }

    public function getPermissionColor(string $category): string
    {
        return StatusHelper::getPermissionColor($category);
    }

    public function getPermissionIcon(string $category): string
    {
        return StatusHelper::getPermissionIcon($category);
    }

    // * ========================================
    // * HELPER METHODS
    // * ========================================

    private function hasUsages(): bool
    {
        return $this->permission->roles()->count() > 0 || $this->permission->users()->count() > 0;
    }

    private function getPermissionDisplayName(): string
    {
        return ucwords(str_replace(['-', '_', '.'], ' ', $this->permission->name));
    }

    // * ========================================
    // * COMPUTED PROPERTIES
    // * ========================================

    public function getGuardsProperty(): array
    {
        return [
            'web' => 'Web',
            'api' => 'API',
        ];
    }

    public function getHasChangesProperty(): bool
    {
        return $this->name !== $this->originalName ||
               $this->guard_name !== $this->originalGuardName;
    }

    public function getIsFormValidProperty(): bool
    {
        return !empty($this->name) &&
               !empty($this->guard_name) &&
               strlen($this->name) >= 3 &&
               in_array($this->guard_name, ['web', 'api']) &&
               preg_match('/^[a-z0-9\s\-_\.]+$/i', $this->name);
    }

    public function getCanBeDeletedProperty(): bool
    {
        return StatusHelper::canPermissionBeDeleted($this->permission);
    }

    public function getUsageStatsProperty(): array
    {
        return [
            'roles_count' => $this->permission->roles()->count(),
            'users_count' => $this->permission->users()->count(),
            'total_usage' => $this->permission->roles()->count() + $this->permission->users()->count(),
        ];
    }

    public function getBreadcrumbsProperty(): array
    {
        return [
            [
                'label' => 'Dashboard',
                'url' => route('app.dashboard'),
            ],
            [
                'label' => 'Permission',
                'url' => route('app.permission.index'),
            ],
            [
                'label' => $this->getPermissionDisplayName(),
                'url' => route('app.permission.view', $this->permission),
            ],
            [
                'label' => 'Edit',
                'url' => null,
            ],
        ];
    }

    public function getGuardWarningProperty(): ?string
    {
        if ($this->guard_name !== $this->originalGuardName && $this->hasUsages()) {
            return 'Mengubah guard akan mempengaruhi ' . $this->usageStats['total_usage'] . ' assignment yang ada.';
        }

        return null;
    }

    // * ========================================
    // * RENDER
    // * ========================================

    public function render()
    {
        return view('livewire.app.pages.permission.edit', [
            'guards' => $this->guards,
            'hasChanges' => $this->hasChanges,
            'isFormValid' => $this->isFormValid,
            'canBeDeleted' => $this->canBeDeleted,
            'usageStats' => $this->usageStats,
            'breadcrumbs' => $this->breadcrumbs,
            'guardWarning' => $this->guardWarning,
        ]);
    }
}
