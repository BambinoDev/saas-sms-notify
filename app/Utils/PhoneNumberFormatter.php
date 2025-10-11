<?php

namespace App\Utils;

use Illuminate\Support\Facades\Log;

class PhoneNumberFormatter
{
    private const VALID_PREFIXES = ['01', '05', '07'];
    private const COUNTRY_CODE = '225';
    
    /**
     * Formater un numéro ivoirien au format international
     */
    public static function format(?string $phoneNumber): ?string
    {
        if (empty($phoneNumber)) {
            return null;
        }
        
        // Nettoyer : enlever espaces, tirets, parenthèses
        $cleaned = preg_replace('/[^0-9+]/', '', $phoneNumber);
        
        // Enlever le + si présent pour traiter uniformément
        $cleaned = ltrim($cleaned, '+');
        
        // Cas 1 : Commence par 225 (avec ou sans +)
        if (str_starts_with($cleaned, self::COUNTRY_CODE)) {
            $localNumber = substr($cleaned, 3);
        }
        // Cas 2 : Commence par 0 (format local)
        elseif (str_starts_with($cleaned, '0')) {
            $localNumber = $cleaned;
        }
        // Cas 3 : Autre format
        else {
            $localNumber = $cleaned;
        }
        
        // VALIDATION STRICTE : 10 chiffres exactement
        if (strlen($localNumber) !== 10) {
            Log::warning('Numéro invalide - longueur incorrecte', [
                'original' => $phoneNumber,
                'cleaned' => $localNumber,
                'length' => strlen($localNumber),
                'expected' => 10
            ]);
            return null;
        }
        
        // Validation du préfixe opérateur
        $prefix = substr($localNumber, 0, 2);
        if (!in_array($prefix, self::VALID_PREFIXES)) {
            Log::warning('Numéro invalide - préfixe opérateur incorrect', [
                'original' => $phoneNumber,
                'prefix' => $prefix,
                'valid_prefixes' => self::VALID_PREFIXES
            ]);
            return null;
        }
        
        // Format final : +225XXXXXXXXXX
        return '+' . self::COUNTRY_CODE . $localNumber;
    }
    
    /**
     * Valider un numéro sans le formatter
     */
    public static function isValid(?string $phoneNumber): bool
    {
        return self::format($phoneNumber) !== null;
    }
    
    /**
     * Obtenir les statistiques de validation
     */
    public static function getValidationStats(): array
    {
        $total = \App\Models\Woman::count();
        $withPhone = \App\Models\Woman::whereNotNull('contact_phone_number')->count();
        $withoutPhone = $total - $withPhone;
        
        return [
            'total_women' => $total,
            'with_phone' => $withPhone,
            'without_phone' => $withoutPhone,
            'percentage_with_phone' => $total > 0 ? round(($withPhone / $total) * 100, 2) : 0,
        ];
    }
    
    /**
     * Tester plusieurs formats de numéros
     */
    public static function testFormats(): array
    {
        $testNumbers = [
            '0787487706',      // Format local valide
            '2250787487706',   // Format international sans +
            '+2250787487706',  // Format international avec +
            '078748770',       // Trop court
            '0987487706',      // Préfixe invalide
            '07874877061',     // Trop long
            '',                // Vide
            null,              // Null
            '078-748-7706',    // Avec tirets
            '078 748 7706',    // Avec espaces
            '(078) 748-7706',  // Avec parenthèses
        ];
        
        $results = [];
        foreach ($testNumbers as $number) {
            $formatted = self::format($number);
            $results[] = [
                'original' => $number,
                'formatted' => $formatted,
                'valid' => $formatted !== null,
            ];
        }
        
        return $results;
    }
}
