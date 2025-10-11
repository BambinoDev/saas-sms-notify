<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Jobs\SendPendingSmsJob;
use App\Services\SmsService;

class SendSmsCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'sms:send 
                            {--limit=100 : Maximum number of SMS to send}
                            {--async : Run in background queue}';
    
    /**
     * The console command description.
     */
    protected $description = 'Send pending SMS from queue';

    /**
     * Execute the console command.
     */
    public function handle(SmsService $smsService)
    {
        $this->info('╔════════════════════════════════════════╗');
        $this->info('║   Send SMS - S-Remind System          ║');
        $this->info('╚════════════════════════════════════════╝');
        $this->newLine();

        $limit = (int) $this->option('limit');

        if ($this->option('async')) {
            SendPendingSmsJob::dispatch($limit);
            $this->info('✅ Job dispatched to queue');
            $this->info('📋 Check logs: tail -f storage/logs/laravel.log');
        } else {
            $this->info("⏳ Sending up to {$limit} SMS (synchronous)...");
            $this->newLine();
            
            $stats = $smsService->sendPendingSms($limit);
            
            $this->newLine();
            $this->info("✅ SMS Sending Complete:");
            $this->line("   Processed: {$stats['processed']}");
            $this->line("   Sent: {$stats['sent']}");
            $this->line("   Failed: {$stats['failed']}");
            $this->line("   Skipped: {$stats['skipped']}");
        }

        return 0;
    }
}
