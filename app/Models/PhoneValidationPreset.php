<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PhoneValidationPreset extends Model
{
    protected $fillable = [
        'country_iso',
        'country_name',
        'country_code',
        'national_length',
        'min_length',
        'max_length',
        'mobile_prefixes',
        'fixed_prefixes',
        'premium_prefixes',
        'operators',
        'validation_regex',
        'notes',
        'is_active',
    ];

    protected $casts = [
        'mobile_prefixes' => 'array',
        'fixed_prefixes' => 'array',
        'premium_prefixes' => 'array',
        'operators' => 'array',
        'is_active' => 'boolean',
    ];

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByCountry($query, string $countryIso)
    {
        return $query->where('country_iso', $countryIso);
    }

    // Methods
    public function getMobilePrefixesForCountry(): array
    {
        return $this->mobile_prefixes ?? [];
    }

    public function isValidMobilePrefix(string $prefix): bool
    {
        return in_array($prefix, $this->getMobilePrefixesForCountry());
    }

    public function getOperatorForPrefix(string $prefix): ?string
    {
        if (!$this->operators) {
            return null;
        }

        foreach ($this->operators as $operator => $prefixes) {
            if (in_array($prefix, $prefixes)) {
                return $operator;
            }
        }

        return null;
    }
}
