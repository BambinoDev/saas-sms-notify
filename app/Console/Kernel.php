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
        // ============================================
        // GÉNÉRATION AUTOMATIQUE SMS
        // ============================================
        
        // Option 1 : Toutes les 15 minutes (recommandé pour démo)
        $schedule->job(new \App\Jobs\GenerateSmsFromRulesJob)
            ->everyFifteenMinutes()
            ->withoutOverlapping()
            ->onOneServer()
            ->timezone('Africa/Abidjan')
            ->appendOutputTo(storage_path('logs/scheduler.log'));

        // Option 2 : Toutes les heures (production)
        // $schedule->job(new \App\Jobs\GenerateSmsFromRulesJob)
        //     ->hourly()
        //     ->withoutOverlapping()
        //     ->onOneServer()
        //     ->timezone('Africa/Abidjan');

        // Option 3 : Une fois par jour à 01h00 (si règles toutes daily)
        // $schedule->job(new \App\Jobs\GenerateSmsFromRulesJob)
        //     ->dailyAt('01:00')
        //     ->withoutOverlapping()
        //     ->onOneServer()
        //     ->timezone('Africa/Abidjan');

        // ============================================
        // SYNCHRONISATION COMMCARE (existant)
        // ============================================
        
        // Garder votre synchronisation CommCare existante
        $schedule->job(new \App\Jobs\SyncCommCareCasesJob)
            ->dailyAt('02:30')
            ->timezone('Africa/Abidjan');
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
