<?php

namespace App\Livewire\App\Pages\Driver;

use App\Models\User;
use App\Class\Helper\UserHelper;
use App\Class\Helper\DriverHelper;
use App\Class\Helper\FormatHelper;
use Mary\Traits\Toast;
use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Computed;

#[Title('Detail Sopir')]
#[Layout('livewire.layouts.app')]
class View extends Component
{
   use Toast;

   public User $user;

   public function mount(User $user): void
   {
       if (!$user->isDriver()) {
           $this->error('User ini bukan driver.', position: 'toast-top toast-end');
           $this->redirect(route('app.driver.index'), navigate: true);
           return;
       }

       $this->user = $user;
   }

   public function editDriver(): void
   {
       $this->redirect(route('app.driver.edit', $this->user), navigate: true);
   }

   public function backToList(): void
   {
       $this->redirect(route('app.driver.index'), navigate: true);
   }

   public function changeUserStatus(): void
   {
       $this->dispatch('openChangeStatusModal', $this->user->id);
   }

   public function deleteUser(): void
   {
       $this->dispatch('openDeleteUserModal', $this->user->id);
   }

   protected $listeners = [
       'userStatusUpdated' => '$refresh',
       'userDeleted' => 'handleUserDeleted'
   ];

   public function handleUserDeleted(): void
   {
       $this->success('Driver berhasil dihapus.', position: 'toast-top toast-end');
       $this->redirect(route('app.driver.index'), navigate: true);
   }

   /**
    * DYNAMIC: User role info menggunakan UserHelper
    */
   #[Computed]
   public function userRoleInfo(): array
   {
       return [
           'label' => UserHelper::getRoleLabel($this->user->role),
           'color' => UserHelper::getRoleColor($this->user->role),
           'icon' => UserHelper::getRoleIcon($this->user->role),
           'description' => UserHelper::getRoleDescription($this->user->role),
       ];
   }

   /**
    * DYNAMIC: User status info menggunakan UserHelper
    */
   #[Computed]
   public function userStatusInfo(): array
   {
       return [
           'label' => UserHelper::getStatusLabel($this->user->is_active),
           'color' => UserHelper::getStatusColor($this->user->is_active),
           'icon' => $this->user->is_active ? UserHelper::getStatusIcon('active') : UserHelper::getStatusIcon('inactive'),
       ];
   }

   /**
    * NEW: Driver UI icons & colors dari DriverHelper
    */
   #[Computed]
   public function driverUIConfig(): array
   {
       return [
           'icons' => [
               'phone' => DriverHelper::getDriverFieldIcon('phone'),
               'address' => DriverHelper::getDriverFieldIcon('address'),
               'license_type' => DriverHelper::getDriverFieldIcon('license_type'),
               'license_number' => DriverHelper::getDriverFieldIcon('license_number'),
               'license_expiry' => DriverHelper::getDriverFieldIcon('license_expiry'),
               'license_status' => DriverHelper::getDriverFieldIcon('license_status'),
               'vehicle_type' => DriverHelper::getDriverFieldIcon('vehicle_type'),
               'vehicle_plate' => DriverHelper::getDriverFieldIcon('vehicle_plate'),
               'vehicle_status' => DriverHelper::getDriverFieldIcon('vehicle_status'),
               'driver_display' => DriverHelper::getDriverFieldIcon('driver_display'),
               'edit' => FormatHelper::getCommonIcon('edit'),
               'delete' => FormatHelper::getCommonIcon('delete'),
               'back' => FormatHelper::getCommonIcon('back'),
               'view' => FormatHelper::getCommonIcon('view'),
               'pause' => 'phosphor.pause',
               'play' => 'phosphor.play',
           ],
           'colors' => [
               'phone' => DriverHelper::getDriverFieldColor('phone'),
               'address' => DriverHelper::getDriverFieldColor('address'),
               'license_number' => DriverHelper::getDriverFieldColor('license_number'),
               'license_expiry' => DriverHelper::getDriverFieldColor('license_expiry'),
               'vehicle_type' => DriverHelper::getDriverFieldColor('vehicle_type'),
               'vehicle_plate' => DriverHelper::getDriverFieldColor('vehicle_plate'),
               'vehicle_status' => DriverHelper::getDriverFieldColor('vehicle_status'),
               'driver_display' => DriverHelper::getDriverFieldColor('driver_display'),
           ]
       ];
   }

   /**
    * ENHANCED: License info dengan icon dari helper
    */
   #[Computed]
   public function licenseInfo(): array
   {
       $driver = $this->user->driver;

       if (!$driver || !$driver->license_expiry) {
           return [
               'status' => 'no_license',
               'label' => DriverHelper::getLicenseStatusLabel('no_license'),
               'color' => 'error',
               'icon' => DriverHelper::getLicenseStatusIcon('no_license'),
               'daysToExpiry' => 0,
               'isExpired' => true,
               'isExpiringSoon' => false,
               'statusMessage' => 'Driver belum memiliki data SIM',
               'showWarning' => false,
           ];
       }

       // GUNAKAN helper dengan icon
       $licenseStatus = DriverHelper::getLicenseStatus($driver->license_expiry);
       $daysToExpiry = DriverHelper::getDaysToExpiry($driver->license_expiry);
       $isExpired = DriverHelper::isLicenseExpired($driver->license_expiry);
       $isExpiringSoon = DriverHelper::isLicenseExpiringSoon($driver->license_expiry, DriverHelper::LICENSE_WARNING_DAYS);

       // Status message berdasarkan kondisi
       $statusMessage = match($licenseStatus['status']) {
           'expired' => 'SIM sudah kadaluarsa dan perlu diperpanjang segera',
           'expiring_soon' => 'SIM akan kadaluarsa dalam ' . DriverHelper::LICENSE_WARNING_DAYS . ' hari ke depan, segera perpanjang',
           'valid' => 'SIM masih berlaku dan dapat digunakan untuk beroperasi',
           default => 'Status SIM tidak diketahui'
       };

       return [
           'status' => $licenseStatus['status'],
           'label' => $licenseStatus['label'],
           'color' => $licenseStatus['color'],
           'icon' => $licenseStatus['icon'], // NEW: Icon dari helper
           'daysToExpiry' => $daysToExpiry,
           'isExpired' => $isExpired,
           'isExpiringSoon' => $isExpiringSoon,
           'statusMessage' => $statusMessage,
           'showWarning' => $isExpiringSoon && !$isExpired,
       ];
   }

   /**
    * SIMPLIFIED: User activity
    */
   #[Computed]
   public function userActivity(): array
   {
       $createdAt = $this->user->created_at;
       $updatedAt = $this->user->updated_at;

       $joinedData = $this->getTimeDisplay($createdAt);
       $updateData = $this->getTimeDisplay($updatedAt);

       return [
           'joinedDays' => $joinedData['value'],
           'lastUpdateDays' => $updateData['value'] . ' lalu',
           'isEmailVerified' => !is_null($this->user->email_verified_at),
           'accountAge' => $joinedData['description'],
           'lastUpdate' => $updateData['description'],
       ];
   }

   /**
    * SIMPLIFIED: Profile completeness
    */
   #[Computed]
   public function profileCompleteness(): array
   {
       $completeness = 0;
       $completeness += $this->user->name ? 20 : 0;
       $completeness += $this->user->email ? 20 : 0;
       $completeness += $this->user->driver && $this->user->driver->license_number ? 20 : 0;
       $completeness += $this->user->driver && $this->user->driver->phone ? 20 : 0;
       $completeness += $this->user->driver && $this->user->driver->vehicle_type ? 20 : 0;

       $description = match(true) {
           $completeness == 100 => 'Profil lengkap dan siap beroperasi',
           $completeness >= 80 => 'Profil hampir lengkap, tinggal sedikit lagi',
           $completeness >= 60 => 'Profil cukup lengkap, tambahkan info kendaraan',
           default => 'Profil perlu dilengkapi lebih banyak'
       };

       return [
           'percentage' => $completeness,
           'description' => $description,
       ];
   }

   /**
    * SIMPLIFIED: Account health assessment
    */
   #[Computed]
   public function accountHealth(): array
   {
       $health = match(true) {
           !$this->user->is_active => 'poor',
           !$this->user->email_verified_at => 'fair',
           $this->user->driver && $this->user->driver->isLicenseExpired() => 'poor',
           $this->user->driver && $this->user->driver->isLicenseExpiringSoon() => 'good',
           default => 'excellent'
       };

       $healthData = [
           'excellent' => [
               'label' => 'Sangat Baik',
               'color' => 'success',
               'description' => 'Semua komponen akun dalam kondisi optimal'
           ],
           'good' => [
               'label' => 'Baik',
               'color' => 'info',
               'description' => 'Akun baik, ada beberapa hal yang perlu dimonitor'
           ],
           'fair' => [
               'label' => 'Cukup',
               'color' => 'warning',
               'description' => 'Akun cukup baik, namun perlu verifikasi email'
           ],
           'poor' => [
               'label' => 'Perlu Perhatian',
               'color' => 'error',
               'description' => 'Akun memerlukan perhatian segera untuk kembali aktif'
           ]
       ];

       return [
           'status' => $health,
           'label' => $healthData[$health]['label'],
           'color' => $healthData[$health]['color'],
           'description' => $healthData[$health]['description'],
       ];
   }

   /**
    * ENHANCED: Vehicle status info dengan icons
    */
   #[Computed]
   public function vehicleInfo(): array
   {
       $driver = $this->user->driver;

       if (!$driver) {
           return [
               'hasVehicle' => false,
               'status' => DriverHelper::getVehicleStatusLabel('none'),
               'description' => 'Driver belum memiliki informasi kendaraan',
               'icon' => DriverHelper::getVehicleStatusIcon('none'),
           ];
       }

       $hasType = !empty($driver->vehicle_type);
       $hasPlate = !empty($driver->vehicle_plate);

       $status = match(true) {
           $hasType && $hasPlate => DriverHelper::getVehicleStatusLabel('complete'),
           $hasType && !$hasPlate => 'Kendaraan Tanpa Plat',
           !$hasType && $hasPlate => 'Plat Tanpa Jenis',
           default => DriverHelper::getVehicleStatusLabel('none')
       };

       $description = match(true) {
           $hasType && $hasPlate => 'Sopir telah terdaftar dengan kendaraan dan siap beroperasi',
           $hasType && !$hasPlate => 'Perlu menambahkan nomor plat kendaraan',
           !$hasType && $hasPlate => 'Perlu menambahkan jenis kendaraan',
           default => 'Perlu menambahkan informasi kendaraan'
       };

       $vehicleStatusType = match(true) {
           $hasType && $hasPlate => 'complete',
           $hasType || $hasPlate => 'partial',
           default => 'none'
       };

       return [
           'hasVehicle' => $hasType || $hasPlate,
           'status' => $status,
           'description' => $description,
           'hasType' => $hasType,
           'hasPlate' => $hasPlate,
           'icon' => DriverHelper::getVehicleStatusIcon($vehicleStatusType), // NEW: Icon
       ];
   }

   /**
    * CORRECTED: Time display method untuk Carbon 3
    */
   private function getTimeDisplay($date): array
   {
       $now = now();

       // Cast ke integer dan gunakan absolute true
       $totalMinutes = (int) $date->diffInMinutes($now, true);
       $totalHours = (int) $date->diffInHours($now, true);
       $totalDays = (int) $date->diffInDays($now, true);
       $totalMonths = (int) $date->diffInMonths($now, true);
       $totalYears = (int) $date->diffInYears($now, true);

       if ($totalYears >= 1) {
           $remainingMonths = $totalMonths - ($totalYears * 12);
           $remainingDays = $totalDays - ($totalYears * 365) - ($remainingMonths * 30);
           return [
               'value' => $totalYears . ' tahun',
               'description' => $remainingMonths > 0 ? "$remainingMonths bulan $remainingDays hari" : "$remainingDays hari"
           ];
       } elseif ($totalMonths >= 1) {
           $remainingDays = $totalDays - ($totalMonths * 30);
           return [
               'value' => $totalMonths . ' bulan',
               'description' => $remainingDays > 0 ? "$remainingDays hari" : ''
           ];
       } elseif ($totalDays >= 1) {
           $remainingHours = $totalHours - ($totalDays * 24);
           return [
               'value' => $totalDays . ' hari',
               'description' => "$remainingHours jam"
           ];
       } elseif ($totalHours >= 1) {
           $remainingMinutes = $totalMinutes - ($totalHours * 60);
           return [
               'value' => $totalHours . ' jam',
               'description' => "$remainingMinutes menit"
           ];
       } elseif ($totalMinutes >= 1) {
           return [
               'value' => $totalMinutes . ' menit',
               'description' => ''
           ];
       } else {
           $totalSeconds = (int) $date->diffInSeconds($now, true);
           return [
               'value' => $totalSeconds . ' detik',
               'description' => ''
           ];
       }
   }

   public function render()
   {
       return view('livewire.app.pages.driver.view');
   }
}
