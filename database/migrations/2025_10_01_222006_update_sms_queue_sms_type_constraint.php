<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Supprimer l'ancienne contrainte
        DB::statement('ALTER TABLE sms_queue DROP CONSTRAINT IF EXISTS sms_queue_sms_type_check');
        
        // Créer une nouvelle contrainte plus flexible
        // On accepte maintenant tous les types de règles dynamiques
        DB::statement("
            ALTER TABLE sms_queue 
            ADD CONSTRAINT sms_queue_sms_type_check 
            CHECK (sms_type ~ '^[a-z0-9-]+$')
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Supprimer la nouvelle contrainte
        DB::statement('ALTER TABLE sms_queue DROP CONSTRAINT IF EXISTS sms_queue_sms_type_check');
        
        // Restaurer l'ancienne contrainte
        DB::statement("
            ALTER TABLE sms_queue 
            ADD CONSTRAINT sms_queue_sms_type_check 
            CHECK (sms_type IN ('j-2', 'jour-j'))
        ");
    }
};