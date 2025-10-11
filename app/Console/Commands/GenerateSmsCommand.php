<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Jobs\GenerateSmsJob;
use App\Services\SmsGenerationService;

class GenerateSmsCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'sms:generate {--async : Run in background queue}';

    /**
     * The console command description.
     */
    protected $description = 'Generate SMS reminders based on active rules';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('╔════════════════════════════════════════╗');
        $this->info('║   SMS Generation - S-Remind System   ║');
        $this->info('╚════════════════════════════════════════╝');
        $this->newLine();

        if ($this->option('async')) {
            // Dispatch to queue
            $this->info('📤 Dispatching SMS generation job to queue...');
            GenerateSmsJob::dispatch();
            $this->info('✅ Job dispatched successfully!');
            $this->info('📋 Monitor progress: tail -f storage/logs/laravel.log');
        } else {
            // Run synchronously
            $this->info('🔄 Starting synchronous generation...');
            $this->newLine();

            // Execute directly
            $job = new GenerateSmsJob();
            $job->handle(app(SmsGenerationService::class));

            $this->newLine();
            $this->info('✅ SMS generation completed!');
        }

        $this->newLine();
        $this->info('📊 Check logs for statistics:');
        $this->line('   - Total generated');
        $this->line('   - Total skipped (duplicates)');
        $this->line('   - Total errors');

        return 0;
    }
}
