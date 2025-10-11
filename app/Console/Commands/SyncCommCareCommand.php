<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Jobs\FetchCommCareDataJob;

class SyncCommCareCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'commcare:sync 
                            {--force : Force sync even if recent sync exists}
                            {--async : Run sync in background queue}';

    /**
     * The console command description.
     */
    protected $description = 'Sync cases from CommCare API';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('╔════════════════════════════════════════╗');
        $this->info('║   CommCare Sync - S-Remind System    ║');
        $this->info('╚════════════════════════════════════════╝');
        $this->newLine();

        if ($this->option('async')) {
            // Run in background queue
            $this->info('📤 Dispatching sync job to queue...');
            FetchCommCareDataJob::dispatch();
            $this->info('✅ Job dispatched successfully!');
            $this->info('📋 Monitor progress: tail -f storage/logs/laravel.log');
        } else {
            // Run synchronously
            $this->info('🔄 Starting synchronous sync...');
            $this->newLine();

            // Progress bar setup
            $this->output->progressStart();

            // Dispatch and wait
            FetchCommCareDataJob::dispatchSync();

            $this->output->progressFinish();
            $this->newLine();
            $this->info('✅ Sync completed!');
        }

        $this->newLine();
        $this->info('📊 Check logs for details:');
        $this->line('   - storage/logs/laravel.log (main log)');
        $this->line('   - storage/logs/commcare-sync.log (scheduled runs)');

        return 0;
    }
}
