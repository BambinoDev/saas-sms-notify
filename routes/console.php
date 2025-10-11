<?php

use App\Jobs\FetchCommCareDataJob;
use App\Jobs\GenerateSmsJob;
use App\Jobs\SendPendingSmsJob;
use App\Jobs\ArchiveOldSmsJob;
use App\Jobs\CleanupOldSmsJob;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Facades\Log;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Synchronisation CommCare - Tous les jours à 02:30 UTC (Africa/Abidjan)
Schedule::job(new FetchCommCareDataJob())
    ->dailyAt('02:30')
    ->timezone('Africa/Abidjan')
    ->name('sync-commcare')
    ->onSuccess(function () {
        Log::info('Scheduler: Sync CommCare réussie');
    })
    ->onFailure(function () {
        Log::error('Scheduler: Sync CommCare échouée');
    });

// SMS Generation - Every day at 05:00 UTC (Africa/Abidjan)
Schedule::job(new GenerateSmsJob())
    ->dailyAt('05:00')
    ->timezone('Africa/Abidjan')
    ->name('generate-sms')
    ->onSuccess(function () {
        Log::info('Scheduler: SMS generation réussie');
    })
    ->onFailure(function () {
        Log::error('Scheduler: SMS generation échouée');
    });

// Send Pending SMS - Every 5 minutes during send window (9h-21h)
Schedule::job(new SendPendingSmsJob(100))
    ->everyFiveMinutes()
    ->between('09:00', '21:00')
    ->timezone('Africa/Abidjan')
    ->name('send-pending-sms')
    ->onSuccess(function () {
        Log::info('Scheduler: SMS sending successful');
    })
    ->onFailure(function () {
        Log::error('Scheduler: SMS sending failed');
    });

// Cleanup logs - Tous les dimanches à 3h du matin
Schedule::job(new CleanupOldSmsJob(90))
    ->weeklyOn(0, '03:00')
    ->timezone('Africa/Abidjan')
    ->name('cleanup-old-sms')
    ->onSuccess(function () {
        Log::info('Scheduler: Cleanup SMS réussi');
    });

// Archiver SMS anciens - Tous les jours à 23h00
Schedule::job(new ArchiveOldSmsJob())
    ->dailyAt('23:00')
    ->timezone('Africa/Abidjan')
    ->name('archive-old-sms')
    ->onSuccess(function () {
        Log::info('Scheduler: Archivage SMS réussi');
    });

// Vérification dossiers fermés - Dimanche 02h00 (hebdomadaire)
Schedule::call(function () {
    $commcare = app(\App\Services\CommCareService::class);
    $stats = $commcare->markClosedCases();
    
    Log::info('Scheduler: Vérification dossiers fermés terminée', $stats);
    
    if ($stats['marked_closed'] > 0) {
        Log::warning("Attention: {$stats['marked_closed']} dossiers ont été fermés");
    }
})
    ->weeklyOn(0, '02:00') // Dimanche à 02h
    ->timezone('Africa/Abidjan')
    ->name('verify-closed-cases')
    ->onSuccess(function () {
        Log::info('Scheduler: Vérification dossiers fermés réussie');
    })
    ->onFailure(function () {
        Log::error('Scheduler: Vérification dossiers fermés échouée');
    });
