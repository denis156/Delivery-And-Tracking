<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\ConsoleOutput;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    private ConsoleOutput $output;
    private ProgressBar $progressBar;

    public function __construct()
    {
        $this->output = new ConsoleOutput();
    }

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->showWelcomeMessage();

        $seeders = [
            UserSeeder::class => '👥 Creating Users',
            DeliveryOrderSeeder::class => '📦 Creating Delivery Orders',
            DemoDataSeeder::class => '🎭 Creating Demo Data',
        ];

        $totalSteps = count($seeders);
        $this->createProgressBar($totalSteps);

        foreach ($seeders as $seederClass => $message) {
            $this->runSeederWithProgress($seederClass, $message);
        }

        $this->finishProgressBar();
        $this->showCompletionMessage();
    }

    private function showWelcomeMessage(): void
    {
        $this->output->writeln('');
        $this->output->writeln('<fg=cyan>════════════════════════════════════════════════════════</fg=cyan>');
        $this->output->writeln('  <fg=white;options=bold>🚚 ArteliaDev Delivery System</fg=white;options=bold>');
        $this->output->writeln('  <fg=yellow>Database Seeding Process</fg=yellow>');
        $this->output->writeln('<fg=cyan>════════════════════════════════════════════════════════</fg=cyan>');
        $this->output->writeln('');
    }

    private function createProgressBar(int $totalSteps): void
    {
        $this->progressBar = new ProgressBar($this->output, $totalSteps);
        $this->progressBar->setFormat(' %current%/%max% [%bar%] %percent:3s%% %message%');
        $this->progressBar->setMessage('🚀 Initializing...');
        $this->progressBar->start();
    }

    private function runSeederWithProgress(string $seederClass, string $message): void
    {
        $this->progressBar->setMessage($message);

        // Add small delay for visual effect
        usleep(300000); // 0.3 seconds

        $this->call($seederClass);
        $this->progressBar->advance();
    }

    private function finishProgressBar(): void
    {
        $this->progressBar->setMessage('✅ Database seeding completed!');
        $this->progressBar->finish();
        $this->output->writeln('');
    }

    private function showCompletionMessage(): void
    {
        $this->output->writeln('');
        $this->output->writeln('<fg=green>═══════════════════════════════════════════════════════</fg=green>');
        $this->output->writeln('<fg=green>🎉 SUCCESS! Database has been seeded successfully!</fg=green>');
        $this->output->writeln('');
        $this->output->writeln('<fg=yellow>📊 Summary:</fg=yellow>');
        $this->output->writeln('   • Sample users for all roles created');
        $this->output->writeln('   • Delivery orders with various statuses generated');
        $this->output->writeln('   • Complete tracking & history data populated');
        $this->output->writeln('   • Demo workflow scenarios ready for testing');
        $this->output->writeln('');
        $this->output->writeln('<fg=cyan>🔑 Default Login Credentials:</fg=cyan>');
        $this->output->writeln('   • <fg=white>admin@artelia.dev</fg=white> / <fg=green>password</fg=green> (Admin)');
        $this->output->writeln('   • <fg=white>manager@artelia.dev</fg=white> / <fg=green>password</fg=green> (Manager)');
        $this->output->writeln('   • <fg=white>driver@artelia.dev</fg=white> / <fg=green>password</fg=green> (Driver)');
        $this->output->writeln('');
        $this->output->writeln('<fg=magenta>🚀 Ready to start developing!</fg=magenta>');
        $this->output->writeln('<fg=green>═══════════════════════════════════════════════════════</fg=green>');
        $this->output->writeln('');
    }
}
