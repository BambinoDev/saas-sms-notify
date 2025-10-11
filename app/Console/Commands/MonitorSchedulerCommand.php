<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\CaseModel;
use App\Models\SmsQueue;

class MonitorSchedulerCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'scheduler:monitor';

    /**
     * The console command description.
     */
    protected $description = 'Monitor scheduler health and next runs';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('╔════════════════════════════════════════╗');
        $this->info('║   Laravel Scheduler Monitor           ║');
        $this->info('╚════════════════════════════════════════╝');
        $this->newLine();

        // Last CommCare Sync
        $this->info('🔄 Last CommCare Sync:');
        $lastSync = CaseModel::max('updated_at');
        if ($lastSync) {
            $this->line("   {$lastSync}");
            $this->line("   (" . \Carbon\Carbon::parse($lastSync)->diffForHumans() . ")");
        } else {
            $this->line("   No sync yet");
        }
        $this->newLine();

        // Total Cases
        $this->info('📊 Cases Statistics:');
        $totalCases = CaseModel::count();
        $eligibleCases = CaseModel::eligible()->count();
        $this->line("   Total: {$totalCases}");
        $this->line("   Eligible (with phone): {$eligibleCases}");
        $this->newLine();

        // Last SMS Generation
        $this->info('📲 Last SMS Generation:');
        $lastGeneration = SmsQueue::max('created_at');
        if ($lastGeneration) {
            $this->line("   {$lastGeneration}");
            $this->line("   (" . \Carbon\Carbon::parse($lastGeneration)->diffForHumans() . ")");
        } else {
            $this->line("   No SMS generated yet");
        }
        $this->newLine();

        // SMS Stats Today
        $this->info('📊 SMS Generated Today:');
        $todayCount = SmsQueue::whereDate('created_at', today())->count();
        $todayPending = SmsQueue::where('status', 'pending')->whereDate('created_at', today())->count();
        $todaySent = SmsQueue::where('status', 'sent')->whereDate('created_at', today())->count();
        $todayFailed = SmsQueue::where('status', 'failed')->whereDate('created_at', today())->count();
        
        $this->line("   Total: {$todayCount}");
        $this->line("   Pending: {$todayPending}");
        $this->line("   Sent: {$todaySent}");
        $this->line("   Failed: {$todayFailed}");
        $this->newLine();

        // Active Rules
        $this->info('⚙️  Active Rules:');
        $activeRules = \App\Models\SmsRule::where('active', true)->count();
        $this->line("   {$activeRules} active rules");
        $this->newLine();

        // Scheduled Jobs
        $this->info('⏰ Scheduled Jobs:');
        $this->call('schedule:list');

        return 0;
    }
}

