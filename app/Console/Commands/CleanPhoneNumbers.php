<?php

namespace App\Console\Commands;

use App\Models\Woman;
use App\Utils\PhoneNumberFormatter;
use Illuminate\Console\Command;

class CleanPhoneNumbers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'phone:clean {--dry-run : Afficher les changements sans les appliquer}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Nettoyer et reformater tous les numéros de téléphone existants';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🧹 Nettoyage des numéros de téléphone...');
        
        $women = Woman::whereNotNull('contact_phone_number')->get();
        $cleaned = 0;
        $invalidated = 0;
        $unchanged = 0;
        $errors = 0;
        
        $this->info("📊 Traitement de {$women->count()} femmes avec numéros de téléphone...");
        
        $progressBar = $this->output->createProgressBar($women->count());
        $progressBar->start();
        
        foreach ($women as $woman) {
            try {
                $originalContact = $woman->contact_phone_number;
                $originalHusband = $woman->husband_phone_number;
                
                $formattedContact = PhoneNumberFormatter::format($originalContact);
                $formattedHusband = PhoneNumberFormatter::format($originalHusband);
                
                $contactChanged = $formattedContact !== $originalContact;
                $husbandChanged = $formattedHusband !== $originalHusband;
                
                if ($contactChanged || $husbandChanged) {
                    if (!$this->option('dry-run')) {
                        $woman->contact_phone_number = $formattedContact;
                        $woman->husband_phone_number = $formattedHusband;
                        $woman->save();
                    }
                    
                    if ($formattedContact) {
                        $cleaned++;
                    } else {
                        $invalidated++;
                    }
                    
                    if ($formattedHusband && !$formattedContact) {
                        $cleaned++;
                    } elseif (!$formattedHusband && $originalHusband) {
                        $invalidated++;
                    }
                } else {
                    $unchanged++;
                }
                
            } catch (\Exception $e) {
                $errors++;
                $this->error("Erreur pour {$woman->case_name}: " . $e->getMessage());
            }
            
            $progressBar->advance();
        }
        
        $progressBar->finish();
        $this->newLine(2);
        
        // Affichage des résultats
        $this->info("✅ Nettoyage terminé :");
        $this->info("   📱 {$cleaned} numéros reformattés");
        $this->info("   ❌ {$invalidated} numéros invalidés");
        $this->info("   ✅ {$unchanged} numéros inchangés");
        
        if ($errors > 0) {
            $this->warn("   ⚠️  {$errors} erreurs rencontrées");
        }
        
        if ($this->option('dry-run')) {
            $this->warn("🔍 Mode dry-run : Aucun changement appliqué");
        }
        
        // Statistiques finales
        $stats = PhoneNumberFormatter::getValidationStats();
        $this->newLine();
        $this->info("📊 Statistiques finales :");
        $this->info("   👥 Total femmes : {$stats['total_women']}");
        $this->info("   📱 Avec téléphone : {$stats['with_phone']} ({$stats['percentage_with_phone']}%)");
        $this->info("   📵 Sans téléphone : {$stats['without_phone']}");
        
        return 0;
    }
}
