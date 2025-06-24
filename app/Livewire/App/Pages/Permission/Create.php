<?php

namespace App\Livewire\App\Pages\Permission;

use Spatie\Permission\Models\Permission;
use Mary\Traits\Toast;
use App\Class\StatusHelper;
use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;

#[Title('Buat Permission')]
#[Layout('livewire.layouts.app')]
class Create extends Component
{
    use Toast;

    // * ========================================
    // * PROPERTIES (Livewire 3 Standards)
    // * ========================================

    public string $name = '';
    public string $guard_name = 'web';
    public bool $createMultiple = false;
    public string $multipleNames = '';

    // * ========================================
    // * LIFECYCLE HOOKS
    // * ========================================

    public function mount(): void
    {
        $this->guard_name = 'web';
    }

    // * ========================================
    // * VALIDATION RULES
    // * ========================================

    protected function rules(): array
    {
        if ($this->createMultiple) {
            return [
                'multipleNames' => [
                    'required',
                    'string',
                    function ($attribute, $value, $fail) {
                        $names = array_filter(array_map('trim', explode("\n", $value)));
                        if (empty($names)) {
                            $fail('Minimal satu nama permission harus diisi.');
                        }

                        foreach ($names as $name) {
                            if (Permission::where('name', $name)->where('guard_name', $this->guard_name)->exists()) {
                                $fail("Permission '{$name}' sudah ada.");
                            }
                        }
                    }
                ],
                'guard_name' => ['required', 'string', 'in:web,api'],
            ];
        }

        return [
            'name' => [
                'required',
                'string',
                'min:3',
                'max:255',
                'unique:permissions,name,NULL,id,guard_name,' . $this->guard_name,
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
            'multipleNames' => 'daftar permission',
        ];
    }

    protected function messages(): array
    {
        return [
            'name.required' => 'Nama permission wajib diisi.',
            'name.min' => 'Nama permission minimal 3 karakter.',
            'name.max' => 'Nama permission maksimal 255 karakter.',
            'name.unique' => 'Permission dengan nama ini sudah ada.',
            'name.regex' => 'Nama permission hanya boleh berisi huruf, angka, spasi, strip, underscore, dan titik.',

            'guard_name.required' => 'Guard wajib dipilih.',
            'guard_name.in' => 'Guard yang dipilih tidak valid.',

            'multipleNames.required' => 'Daftar permission wajib diisi.',
        ];
    }

    // * ========================================
    // * REAL-TIME VALIDATION
    // * ========================================

    public function updatedName(): void
    {
        if (!$this->createMultiple) {
            $this->validateOnly('name');
        }
    }

    public function updatedGuardName(): void
    {
        if (!$this->createMultiple) {
            $this->validateOnly('name');
        }
    }

    public function updatedCreateMultiple(): void
    {
        $this->resetValidation();
        if ($this->createMultiple) {
            $this->name = '';
        } else {
            $this->multipleNames = '';
        }
    }

    // * ========================================
    // * ACTIONS
    // * ========================================

    public function save(): void
    {
        $this->validate();

        try {
            if ($this->createMultiple) {
                $this->saveMultiple();
            } else {
                $this->saveSingle();
            }
        } catch (\Exception $e) {
            $this->error(
                'Gagal membuat permission! Terjadi kesalahan sistem.',
                position: 'toast-top toast-end'
            );
        }
    }

    private function saveSingle(): void
    {
        $permission = Permission::create([
            'name' => trim($this->name),
            'guard_name' => $this->guard_name,
        ]);

        $this->success(
            "Permission '{$permission->name}' berhasil dibuat!",
            position: 'toast-top toast-end',
            redirectTo: route('app.permission.index')
        );
    }

    private function saveMultiple(): void
    {
        $names = array_filter(array_map('trim', explode("\n", $this->multipleNames)));
        $created = 0;

        foreach ($names as $name) {
            Permission::create([
                'name' => $name,
                'guard_name' => $this->guard_name,
            ]);
            $created++;
        }

        $this->success(
            "Berhasil membuat {$created} permission!",
            position: 'toast-top toast-end',
            redirectTo: route('app.permission.index')
        );
    }

    public function cancel(): void
    {
        $this->redirect(route('app.permission.index'), navigate: true);
    }

    public function resetForm(): void
    {
        $this->reset(['name', 'multipleNames']);
        $this->guard_name = 'web';
        $this->createMultiple = false;
        $this->resetValidation();

        $this->info('Form berhasil direset!', position: 'toast-top toast-end');
    }

    public function useSuggestion(string $suggestion): void
    {
        if ($this->createMultiple) {
            $currentNames = array_filter(array_map('trim', explode("\n", $this->multipleNames)));
            if (!in_array($suggestion, $currentNames)) {
                $this->multipleNames = !empty($this->multipleNames)
                    ? $this->multipleNames . "\n" . $suggestion
                    : $suggestion;
            }
        } else {
            $this->name = $suggestion;
        }
    }

    public function loadCategorySuggestions(string $category): void
    {
        $suggestions = $this->getSuggestionsByCategory($category);

        if ($this->createMultiple) {
            $this->multipleNames = implode("\n", $suggestions);
        } else {
            $this->name = $suggestions[0] ?? '';
        }
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
    // * COMPUTED PROPERTIES
    // * ========================================

    public function getGuardsProperty(): array
    {
        return [
            'web' => 'Web',
            'api' => 'API',
        ];
    }

    public function getSuggestionsProperty(): array
    {
        return [
            'User Management' => [
                'view users',
                'create users',
                'edit users',
                'delete users',
                'manage users',
            ],
            'Role Management' => [
                'view roles',
                'create roles',
                'edit roles',
                'delete roles',
                'manage roles',
            ],
            'Permission Management' => [
                'view permissions',
                'create permissions',
                'edit permissions',
                'delete permissions',
                'manage permissions',
            ],
            'System' => [
                'access system',
                'manage system',
                'view logs',
                'backup system',
            ],
            'Delivery Management' => [
                'view delivery orders',
                'create delivery orders',
                'edit delivery orders',
                'delete delivery orders',
                'manage delivery orders',
            ],
            'Driver Management' => [
                'view drivers',
                'create drivers',
                'edit drivers',
                'delete drivers',
                'manage drivers',
            ],
        ];
    }

    public function getIsFormValidProperty(): bool
    {
        if ($this->createMultiple) {
            return !empty($this->multipleNames) && !empty($this->guard_name);
        }

        return !empty($this->name) && !empty($this->guard_name);
    }

    public function getHasDataProperty(): bool
    {
        return !empty($this->name) || !empty($this->multipleNames);
    }

    // * ========================================
    // * HELPER METHODS
    // * ========================================

    private function getSuggestionsByCategory(string $category): array
    {
        return $this->suggestions[$category] ?? [];
    }

    // * ========================================
    // * RENDER
    // * ========================================

    public function render()
    {
        return view('livewire.app.pages.permission.create', [
            'guards' => $this->guards,
            'suggestions' => $this->suggestions,
            'isFormValid' => $this->isFormValid,
            'hasData' => $this->hasData,
        ]);
    }
}
