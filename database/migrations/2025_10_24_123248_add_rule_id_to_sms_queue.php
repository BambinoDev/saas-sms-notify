<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sms_queue', function (Blueprint $table) {
            // Vérifier si la colonne existe déjà
            if (!Schema::hasColumn('sms_queue', 'rule_id')) {
                $table->unsignedBigInteger('rule_id')->nullable()
                    ->after('organization_id')
                    ->comment('Règle qui a généré ce SMS');
                
                $table->foreign('rule_id')
                    ->references('id')
                    ->on('sms_rules')
                    ->onDelete('set null');
                
                $table->index('rule_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('sms_queue', function (Blueprint $table) {
            if (Schema::hasColumn('sms_queue', 'rule_id')) {
                $table->dropForeign(['rule_id']);
                $table->dropIndex(['rule_id']);
                $table->dropColumn('rule_id');
            }
        });
    }
};