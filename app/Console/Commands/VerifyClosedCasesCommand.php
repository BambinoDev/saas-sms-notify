<?php

namespace App\Console\Commands;

use App\Services\CommCareService;
use Illuminate\Console\Command;

class VerifyClosedCasesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'commcare:verify-closed';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Vérifier et marquer les dossiers fermés via API CommCare';

    /**
     * Execute the console command.
     */
    public function handle(CommCareService $commcareService): int
    {
        $this->info('🔍 Début vérification des dossiers fermés...');

        $stats = $commcareService->markClosedCases();

        $this->newLine();
        $this->info('📊 Résultats :');
        $this->line("  • Dossiers vérifiés : {$stats['checked']}");
        $this->line("  • Dossiers fermés détectés : {$stats['marked_closed']}");
        $this->line("  • Erreurs : {$stats['errors']}");

        if ($stats['marked_closed'] > 0) {
            $this->warn("⚠️  {$stats['marked_closed']} dossiers ont été marqués comme fermés");
        } else {
            $this->info('✅ Aucun dossier fermé détecté');
        }

        return Command::SUCCESS;
    }
}