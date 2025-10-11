<?php

namespace App\Services;

use App\Contracts\SmsGatewayInterface;
use App\Services\SmsGateways\AfricasTalkingGateway;
use App\Models\SmsQueue;
use Illuminate\Support\Facades\Log;

class SmsService
{
    protected $gateway;

    public function __construct()
    {
        // For now, hardcoded Africa's Talking
        // Later: dynamic gateway selection per tenant
        $this->gateway = new AfricasTalkingGateway();
    }

    /**
     * Send pending SMS from queue
     */
    public function sendPendingSms(int $limit = 100): array
    {
        $stats = [
            'processed' => 0,
            'sent' => 0,
            'failed' => 0,
            'skipped' => 0,
        ];

        Log::info('Starting SMS sending process', [
            'limit' => $limit,
        ]);

        // Get pending SMS that should be sent now
        $pendingSms = SmsQueue::where('status', 'pending')
            ->where('scheduled_at', '<=', now())
            ->orderBy('scheduled_at', 'asc')
            ->limit($limit)
            ->get();

        Log::info('Pending SMS found', [
            'count' => $pendingSms->count(),
        ]);

        if ($pendingSms->isEmpty()) {
            Log::info('No pending SMS to send');
            return $stats;
        }

        foreach ($pendingSms as $sms) {
            $stats['processed']++;

            try {
                // Validate phone number
                if (empty($sms->recipient_phone)) {
                    Log::warning('SMS skipped: no phone number', [
                        'sms_id' => $sms->id,
                    ]);
                    $sms->update([
                        'status' => 'failed',
                        'error_message' => 'Phone number is empty',
                    ]);
                    $stats['skipped']++;
                    continue;
                }

                // Mark as sending
                $sms->update(['status' => 'sending']);

                Log::info('Sending SMS', [
                    'sms_id' => $sms->id,
                    'phone' => $sms->recipient_phone,
                    'message_preview' => substr($sms->message_content, 0, 50) . '...',
                ]);

                // Send via gateway
                $result = $this->gateway->send($sms->recipient_phone, $sms->message_content);

                if ($result['success']) {
                    // Success
                    $updateData = [
                        'status' => 'sent',
                        'sent_at' => now(),
                        'error_message' => null,
                    ];

                    // Add external_id and cost if columns exist
                    if (\Schema::hasColumn('sms_queue', 'external_id')) {
                        $updateData['external_id'] = $result['message_id'];
                    }
                    if (\Schema::hasColumn('sms_queue', 'cost')) {
                        $updateData['cost'] = $result['cost'];
                    }

                    $sms->update($updateData);

                    $stats['sent']++;

                    Log::info('SMS sent successfully', [
                        'sms_id' => $sms->id,
                        'phone' => $sms->recipient_phone,
                        'message_id' => $result['message_id'],
                        'cost' => $result['cost'],
                    ]);

                } else {
                    // Failed
                    $sms->update([
                        'status' => 'failed',
                        'error_message' => $result['error'],
                    ]);

                    $stats['failed']++;

                    Log::warning('SMS failed', [
                        'sms_id' => $sms->id,
                        'phone' => $sms->recipient_phone,
                        'error' => $result['error'],
                    ]);
                }

            } catch (\Exception $e) {
                $sms->update([
                    'status' => 'failed',
                    'error_message' => $e->getMessage(),
                ]);

                $stats['failed']++;

                Log::error('SMS sending exception', [
                    'sms_id' => $sms->id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
            }
        }

        Log::info('SMS sending process completed', $stats);

        return $stats;
    }

    /**
     * Get gateway instance
     */
    public function getGateway(): SmsGatewayInterface
    {
        return $this->gateway;
    }

    /**
     * Test gateway connection
     */
    public function testGateway(): array
    {
        return [
            'provider' => $this->gateway->getProviderName(),
            'available' => $this->gateway->isAvailable(),
            'balance' => $this->gateway->getBalance(),
        ];
    }
}
