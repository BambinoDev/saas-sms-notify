<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\SmsService;

class TestSmsGatewayCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'sms:test 
                            {phone? : Phone number to test (optional)}
                            {--balance : Check balance only}';
    
    /**
     * The console command description.
     */
    protected $description = 'Test SMS gateway connection';

    /**
     * Execute the console command.
     */
    public function handle(SmsService $smsService)
    {
        $this->info('╔════════════════════════════════════════╗');
        $this->info('║   SMS Gateway Test - S-Remind         ║');
        $this->info('╚════════════════════════════════════════╝');
        $this->newLine();

        $gateway = $smsService->getGateway();

        // Provider info
        $this->info("Provider: {$gateway->getProviderName()}");
        $this->info("Available: " . ($gateway->isAvailable() ? 'Yes ✅' : 'No ❌'));
        $this->newLine();

        // Balance
        $this->info('💰 Checking balance...');
        $balance = $gateway->getBalance();
        if ($balance !== null) {
            $this->info("   Balance: {$balance} FCFA");
        } else {
            $this->warn("   Balance: Unable to retrieve");
        }
        $this->newLine();

        // Test send (if phone provided)
        if ($this->option('balance')) {
            $this->info('✅ Balance check completed');
            return 0;
        }

        $phone = $this->argument('phone');
        if ($phone) {
            $this->info("📲 Sending test SMS to {$phone}...");
            $this->newLine();
            
            $result = $gateway->send($phone, 'Test SMS from S-Remind system. Africa\'s Talking integration works! 🎉');
            
            if ($result['success']) {
                $this->info("✅ SMS sent successfully!");
                $this->line("   Message ID: {$result['message_id']}");
                $this->line("   Cost: {$result['cost']} FCFA");
            } else {
                $this->error("❌ SMS failed!");
                $this->line("   Error: {$result['error']}");
            }
        } else {
            $this->warn('💡 Tip: Run with phone number to test sending:');
            $this->line('   php artisan sms:test +2250XXXXXXXXX');
        }

        return 0;
    }
}

