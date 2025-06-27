<?php

namespace App\Livewire\App\Pages\User;

use App\Models\User;
use App\Class\Helper\UserHelper;
use Mary\Traits\Toast;
use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;

#[Title('Detail Pengguna')]
#[Layout('livewire.layouts.app')]
class View extends Component
{
   use Toast;

   public User $user;

   public function mount(User $user): void
   {
       if ($user->isDriver() || $user->isClient()) {
           $this->error('Pengguna ini tidak dapat dilihat dari halaman ini.', position: 'toast-top toast-end');
           $this->redirect(route('app.user.index'), navigate: true);
           return;
       }

       $this->user = $user;
   }

   public function editUser(): void
   {
       $this->redirect(route('app.user.edit', $this->user), navigate: true);
   }

   public function backToList(): void
   {
       $this->redirect(route('app.user.index'), navigate: true);
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
       $this->success('User berhasil dihapus.', position: 'toast-top toast-end');
       $this->redirect(route('app.user.index'), navigate: true);
   }

   public function getUserActivityProperty(): array
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

   private function getTimeDisplay($date): array
   {
       $now = now();

       // Cast ke integer untuk menghindari decimal dari Carbon 3.x
       $totalMinutes = (int) $date->diffInMinutes($now, true);
       $totalHours = (int) $date->diffInHours($now, true);
       $totalDays = (int) $date->diffInDays($now, true);
       $totalMonths = (int) $date->diffInMonths($now, true);
       $totalYears = (int) $date->diffInYears($now, true);

       // Logika berurutan dari yang terbesar
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
       return view('livewire.app.pages.user.view');
   }
}
