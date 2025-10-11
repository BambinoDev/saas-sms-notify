<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Tenant;
use App\Models\TenantCommCareConfig;
use App\Services\CommCareSyncService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SyncCommCareJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public Tenant $tenant
    ) {}

    public function handle(CommCareSyncService $service): void
    {
        $config = TenantCommCareConfig::where('tenant_id', $this->tenant->id)->first();

        if (!$config || !$config->auto_sync_enabled) {
            Log::info("CommCare sync skipped for tenant", [
                'tenant_id' => $this->tenant->id,
                'reason' => !$config ? 'No config' : 'Auto sync disabled'
            ]);
            return;
        }

        tenancy()->initialize($this->tenant);

        try {
            $result = $service->setConfig($config)->syncCases($config->sync_batch_size);

            Log::info("CommCare sync completed for tenant", [
                'tenant_id' => $this->tenant->id,
                'result' => $result
            ]);

        } catch (\Exception $e) {
            Log::error("CommCare sync failed for tenant", [
                'tenant_id' => $this->tenant->id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        } finally {
            tenancy()->end();
        }
    }
}

