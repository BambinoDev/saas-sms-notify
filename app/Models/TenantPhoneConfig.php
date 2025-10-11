<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TenantPhoneConfig extends Model
{
    protected $table = 'tenant_phone_configs';
    protected $fillable = [
        'tenant_id',
        'country_iso',
        'country_code',
        'national_length',
        'validation_mode',
        'allowed_prefixes',
        'blocked_prefixes',
        'mobile_only',
        'auto_format_e164',
        'legacy_transformations',
        'custom_regex',
        'fallback_to_lenient',
    ];

    protected $casts = [
        'allowed_prefixes' => 'array',
        'blocked_prefixes' => 'array',
        'mobile_only' => 'boolean',
        'auto_format_e164' => 'boolean',
        'legacy_transformations' => 'array',
        'fallback_to_lenient' => 'boolean',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
