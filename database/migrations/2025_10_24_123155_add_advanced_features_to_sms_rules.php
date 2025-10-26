<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sms_rules', function (Blueprint $table) {
            // ============================================
            // FRÉQUENCE DE GÉNÉRATION (AUTOMATIQUE)
            // ============================================
            
            $table->string('generation_frequency', 50)->default('daily')
                ->after('trigger_unit')
                ->comment('Fréquence de génération : daily/weekly/monthly');
            
            $table->time('generation_time')->default('01:00:00')
                ->after('generation_frequency')
                ->comment('Heure de génération (ex: 01:00 pour 1h du matin)');
            
            $table->integer('generation_day_of_week')->nullable()
                ->after('generation_time')
                ->comment('Jour de la semaine pour weekly (0=dimanche, 1=lundi, etc.)');
            
            $table->integer('generation_day_of_month')->nullable()
                ->after('generation_day_of_week')
                ->comment('Jour du mois pour monthly (1-31)');
            
            // ============================================
            // PAUSE/RESUME
            // ============================================
            
            $table->boolean('is_paused')->default(false)
                ->after('is_active')
                ->comment('Si true, règle temporairement en pause (auto ET manuel désactivés)');
            
            $table->timestamp('paused_at')->nullable()
                ->after('is_paused')
                ->comment('Date de mise en pause');
            
            $table->text('pause_reason')->nullable()
                ->after('paused_at')
                ->comment('Raison de la pause');
            
            // ============================================
            // STATISTIQUES
            // ============================================
            
            $table->integer('total_generated')->default(0)
                ->after('pause_reason')
                ->comment('Total SMS générés (auto + manuel)');
            
            // total_sent et total_failed existent déjà, on ajoute seulement total_delivered
            $table->integer('total_delivered')->default(0)
                ->after('total_failed')
                ->comment('Total SMS délivrés');
            
            // Renommer last_executed_at en last_generated_at pour cohérence
            $table->timestamp('last_generated_at')->nullable()
                ->after('total_delivered')
                ->comment('Dernière génération (auto OU manuel)');
            
            // ============================================
            // PRIORITÉ (pour ordre d'exécution)
            // ============================================
            
            $table->integer('priority')->default(5)
                ->after('last_generated_at')
                ->comment('Priorité (0 = urgent, 10 = low)');
            
            // ============================================
            // LIMITE QUOTIDIENNE (sécurité)
            // ============================================
            
            $table->integer('daily_limit')->nullable()
                ->after('priority')
                ->comment('Limite SMS générés par jour (NULL = illimité)');
            
            // INDEX
            $table->index(['is_active', 'is_paused']);
            $table->index('generation_frequency');
            $table->index('priority');
            $table->index('last_generated_at');
        });
    }

    public function down(): void
    {
        Schema::table('sms_rules', function (Blueprint $table) {
            $table->dropIndex(['is_active', 'is_paused']);
            $table->dropIndex(['generation_frequency']);
            $table->dropIndex(['priority']);
            $table->dropIndex(['last_generated_at']);
            
            $table->dropColumn([
                'generation_frequency',
                'generation_time',
                'generation_day_of_week',
                'generation_day_of_month',
                'is_paused',
                'paused_at',
                'pause_reason',
                'total_generated',
                'total_delivered',
                'last_generated_at',
                'priority',
                'daily_limit',
            ]);
        });
    }
};