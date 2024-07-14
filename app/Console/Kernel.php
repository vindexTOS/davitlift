<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        $schedule->command('transaction:details')->everyFiveMinutes();
         
        $schedule->command('user:subscription-check')->dailyAt('20:05');
        $schedule->command('user:subscription-check')->dailyAt('00:00');
        $schedule->command('user:subscription-check')->dailyAt('00:10');
        $schedule->command('user:subscription-check')->dailyAt('04:00');

        $schedule->command('user:subscription-check')->dailyAt('04:05');

    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
