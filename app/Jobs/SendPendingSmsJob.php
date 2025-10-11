<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use App\Services\SmsService;

class SendPendingSmsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $timeout = 300; // 5 minutes

    protected $limit;

    public function __construct(int $limit = 100)
    {
        $this->limit = $limit;
    }

    public function handle(SmsService $smsService): void
    {
        Log::info('Starting SendPendingSmsJob', [
            'limit' => $this->limit,
        ]);

        $stats = $smsService->sendPendingSms($this->limit);

        Log::info('SendPendingSmsJob completed', $stats);
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('SendPendingSmsJob failed after all retries', [
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
        ]);
    }
}

