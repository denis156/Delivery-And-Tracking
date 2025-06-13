<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\ConsoleOutput;

class UserSeeder extends Seeder
{
    use WithoutModelEvents;

    private ConsoleOutput $output;
    private ProgressBar $progressBar;

    public function __construct()
    {
        $this->output = new ConsoleOutput();
    }

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->showHeader();
        $this->createFixedUsers();
        $this->createRandomUsers();
        $this->showSummary();
    }

    private function showHeader(): void
    {
        $this->output->writeln('');
        $this->output->writeln('<fg=blue>👥 Creating Users...</fg=blue>');
    }

    private function createFixedUsers(): void
    {
        $fixedUsers = [
            [
                'role' => User::ROLE_ADMIN,
                'name' => 'Administrator ArteliaDev',
                'email' => 'admin@artelia.dev',
                'emoji' => '👑',
            ],
            [
                'role' => User::ROLE_MANAGER,
                'name' => 'Manager Operations',
                'email' => 'manager@artelia.dev',
                'emoji' => '💼',
            ],
            [
                'role' => User::ROLE_DRIVER,
                'name' => 'Driver Demo',
                'email' => 'driver@artelia.dev',
                'emoji' => '🚛',
            ],
            [
                'role' => User::ROLE_CLIENT,
                'name' => 'PT. Demo Client',
                'email' => 'client@artelia.dev',
                'emoji' => '🏢',
            ],
            [
                'role' => User::ROLE_PETUGAS_LAPANGAN,
                'name' => 'Petugas Lapangan Demo',
                'email' => 'lapangan@artelia.dev',
                'emoji' => '👷',
            ],
            [
                'role' => User::ROLE_PETUGAS_RUANGAN,
                'name' => 'Petugas Ruangan Demo',
                'email' => 'ruangan@artelia.dev',
                'emoji' => '🏢',
            ],
            [
                'role' => User::ROLE_PETUGAS_GUDANG,
                'name' => 'Petugas Gudang Demo',
                'email' => 'gudang@artelia.dev',
                'emoji' => '📦',
            ],
        ];

        $this->progressBar = new ProgressBar($this->output, count($fixedUsers));
        $this->progressBar->setFormat(' %current%/%max% [%bar%] %percent:3s%% %message%');
        $this->progressBar->start();

        foreach ($fixedUsers as $userData) {
            $this->progressBar->setMessage("Creating {$userData['emoji']} {$userData['name']}");

            User::create([
                'name' => $userData['name'],
                'email' => $userData['email'],
                'password' => Hash::make('password'),
                'role' => $userData['role'],
                'is_active' => true,
                'email_verified_at' => now(),
            ]);

            usleep(200000); // 0.2 second delay for visual effect
            $this->progressBar->advance();
        }

        $this->progressBar->finish();
        $this->output->writeln('');
    }

    private function createRandomUsers(): void
    {
        $this->output->writeln('<fg=cyan>   🎲 Generating additional random users...</fg=cyan>');

        $randomUserCounts = [
            User::ROLE_DRIVER => 15,
            User::ROLE_CLIENT => 8,
            User::ROLE_PETUGAS_LAPANGAN => 5,
            User::ROLE_PETUGAS_RUANGAN => 4,
            User::ROLE_PETUGAS_GUDANG => 3,
            User::ROLE_ADMIN => 2,
        ];

        $totalRandomUsers = array_sum($randomUserCounts);
        $this->progressBar = new ProgressBar($this->output, $totalRandomUsers);
        $this->progressBar->setFormat(' %current%/%max% [%bar%] %percent:3s%% %message%');
        $this->progressBar->start();

        foreach ($randomUserCounts as $role => $count) {
            $roleLabel = User::getRoleLabelByKey($role);

            for ($i = 0; $i < $count; $i++) {
                $this->progressBar->setMessage("Creating random {$roleLabel} #" . ($i + 1));

                $factory = User::factory();

                // Apply role-specific factory method
                $factory = match($role) {
                    User::ROLE_ADMIN => $factory->admin(),
                    User::ROLE_MANAGER => $factory->manager(),
                    User::ROLE_DRIVER => $factory->driver(),
                    User::ROLE_CLIENT => $factory->client(),
                    User::ROLE_PETUGAS_LAPANGAN => $factory->petugasLapangan(),
                    User::ROLE_PETUGAS_RUANGAN => $factory->petugasRuangan(),
                    User::ROLE_PETUGAS_GUDANG => $factory->petugasGudang(),
                    default => $factory,
                };

                $factory->create();

                usleep(100000); // 0.1 second delay
                $this->progressBar->advance();
            }
        }

        $this->progressBar->finish();
        $this->output->writeln('');
    }

    private function showSummary(): void
    {
        $totalUsers = User::count();
        $activeUsers = User::where('is_active', true)->count();

        $this->output->writeln('');
        $this->output->writeln('<fg=green>✅ Users created successfully!</fg=green>');
        $this->output->writeln("   📊 Total users: <fg=yellow>{$totalUsers}</fg=yellow>");
        $this->output->writeln("   ✅ Active users: <fg=green>{$activeUsers}</fg=green>");

        // Show breakdown by role
        $this->output->writeln('');
        $this->output->writeln('<fg=cyan>   📋 Users by role:</fg=cyan>');

        $roleBreakdown = [
            User::ROLE_ADMIN => ['emoji' => '👑', 'label' => 'Administrators'],
            User::ROLE_MANAGER => ['emoji' => '💼', 'label' => 'Managers'],
            User::ROLE_DRIVER => ['emoji' => '🚛', 'label' => 'Drivers'],
            User::ROLE_CLIENT => ['emoji' => '🏢', 'label' => 'Clients'],
            User::ROLE_PETUGAS_LAPANGAN => ['emoji' => '👷', 'label' => 'Field Staff'],
            User::ROLE_PETUGAS_RUANGAN => ['emoji' => '🏢', 'label' => 'Office Staff'],
            User::ROLE_PETUGAS_GUDANG => ['emoji' => '📦', 'label' => 'Warehouse Staff'],
        ];

        foreach ($roleBreakdown as $role => $info) {
            $count = User::where('role', $role)->count();
            $this->output->writeln("   {$info['emoji']} {$info['label']}: <fg=yellow>{$count}</fg=yellow>");
        }

        $this->output->writeln('');
        $this->output->writeln('<fg=magenta>🔐 Default credentials ready for testing!</fg=magenta>');
    }
}
