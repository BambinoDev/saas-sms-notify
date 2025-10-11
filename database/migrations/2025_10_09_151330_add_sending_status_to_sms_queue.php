<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Add 'sending' status to sms_queue.status enum
     */
    public function up(): void
    {
        // PostgreSQL: Alter ENUM type by recreating it
        DB::statement("ALTER TABLE sms_queue DROP CONSTRAINT sms_queue_status_check");
        DB::statement("ALTER TABLE sms_queue ADD CONSTRAINT sms_queue_status_check CHECK (status IN ('pending', 'sending', 'sent', 'failed', 'delivered'))");
    }

    /**
     * Reverse the migration
     */
    public function down(): void
    {
        // Revert any 'sending' status back to 'pending'
        DB::statement("UPDATE sms_queue SET status = 'pending' WHERE status = 'sending'");
        
        // Remove 'sending' from enum
        DB::statement("ALTER TABLE sms_queue DROP CONSTRAINT sms_queue_status_check");
        DB::statement("ALTER TABLE sms_queue ADD CONSTRAINT sms_queue_status_check CHECK (status IN ('pending', 'sent', 'failed', 'delivered'))");
    }
};

