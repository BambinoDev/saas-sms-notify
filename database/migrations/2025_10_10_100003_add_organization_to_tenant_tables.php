<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Ajouter organization_id aux tables tenant
        $tenantTables = ['women', 'sms_queue', 'sms_rules'];

        foreach ($tenantTables as $table) {
            Schema::table($table, function (Blueprint $table) {
                $table->foreignId('organization_id')
                    ->nullable()
                    ->after('id')
                    ->constrained()
                    ->onDelete('cascade');
                
                $table->index('organization_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tenantTables = ['women', 'sms_queue', 'sms_rules'];

        foreach ($tenantTables as $table) {
            Schema::table($table, function (Blueprint $table) {
                $table->dropForeign(['organization_id']);
                $table->dropIndex(['organization_id']);
                $table->dropColumn('organization_id');
            });
        }
    }
};

