<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\TenantPhoneConfig;
use App\Models\PhoneValidationPreset;
use libphonenumber\PhoneNumberUtil;
use libphonenumber\PhoneNumberFormat;

class PhoneValidationService
{
    private PhoneNumberUtil $phoneUtil;
    private ?TenantPhoneConfig $config = null;

    public function __construct()
    {
        $this->phoneUtil = PhoneNumberUtil::getInstance();
    }

    public function setConfig(TenantPhoneConfig $config): self
    {
        $this->config = $config;
        return $this;
    }

    public function validate(string $phoneNumber): array
    {
        if (!$this->config) {
            throw new \Exception('Phone config not set. Call setConfig() first.');
        }

        try {
            // Parse avec libphonenumber
            $parsed = $this->phoneUtil->parse($phoneNumber, $this->config->country_iso);
            
            // Validation de base
            if (!$this->phoneUtil->isValidNumber($parsed)) {
                return [
                    'valid' => false,
                    'formatted' => null,
                    'error' => 'Invalid phone number format'
                ];
            }

            // Format E.164
            $formatted = $this->phoneUtil->format($parsed, PhoneNumberFormat::E164);
            
            // Validation custom selon config tenant
            if (!$this->validateAgainstTenantRules($formatted)) {
                return [
                    'valid' => false,
                    'formatted' => $formatted,
                    'error' => 'Number does not match tenant validation rules'
                ];
            }

            return [
                'valid' => true,
                'formatted' => $formatted,
                'error' => null
            ];

        } catch (\Exception $e) {
            return [
                'valid' => false,
                'formatted' => null,
                'error' => $e->getMessage()
            ];
        }
    }

    private function validateAgainstTenantRules(string $e164Number): bool
    {
        // Extraire le numéro national (sans country code)
        $nationalNumber = substr($e164Number, strlen($this->config->country_code));
        
        // Vérifier longueur
        if (strlen($nationalNumber) !== $this->config->national_length) {
            return false;
        }

        // Vérifier préfixes autorisés
        if ($this->config->allowed_prefixes) {
            $prefix = substr($nationalNumber, 0, 2);
            if (!in_array($prefix, $this->config->allowed_prefixes)) {
                return false;
            }
        }

        // Vérifier préfixes bloqués
        if ($this->config->blocked_prefixes) {
            $prefix = substr($nationalNumber, 0, 2);
            if (in_array($prefix, $this->config->blocked_prefixes)) {
                return false;
            }
        }

        // Mobile only
        if ($this->config->mobile_only) {
            $preset = PhoneValidationPreset::on('pgsql')
                ->where('country_iso', $this->config->country_iso)
                ->first();
            if ($preset) {
                $prefix = substr($nationalNumber, 0, 2);
                if (!in_array($prefix, $preset->mobile_prefixes)) {
                    return false;
                }
            }
        }

        return true;
    }

    public function formatBatch(array $phoneNumbers): array
    {
        $results = [];
        foreach ($phoneNumbers as $phone) {
            $results[] = $this->validate($phone);
        }
        return $results;
    }
}
