<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\SmsQueue;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendSmsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public SmsQueue $sms
    ) {}

    public function handle(): void
    {
        try {
            // TODO: Intégration gateway SMS (Twilio/Vonage)
            // Pour l'instant, on simule l'envoi
            
            $this->sms->update([
                'status' => 'sent',
                'sent_at' => now(),
                'gateway_provider' => 'simulated',
                'gateway_message_id' => 'sim_' . uniqid(),
            ]);

            Log::info("SMS sent", [
                'sms_id' => $this->sms->id,
                'phone' => $this->sms->phone_number,
                'message' => substr($this->sms->message, 0, 50) . '...'
            ]);

        } catch (\Exception $e) {
            $this->sms->update([
                'status' => 'failed',
                'failed_at' => now(),
                'error_code' => 'SEND_ERROR',
                'error_message' => $e->getMessage(),
                'retry_count' => $this->sms->retry_count + 1,
            ]);

            Log::error("SMS send failed", [
                'sms_id' => $this->sms->id,
                'error' => $e->getMessage()
            ]);

            throw $e;
        }
    }
}

