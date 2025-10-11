<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class AppSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'value',
        'type',
        'description',
    ];

    // Méthodes statiques pour accès facile
    public static function get(string $key, $default = null)
    {
        return Cache::remember("setting.{$key}", 3600, function() use ($key, $default) {
            $setting = self::where('key', $key)->first();
            
            if (!$setting) {
                return $default;
            }

            return self::castValue($setting->value, $setting->type);
        });
    }

    public static function set(string $key, $value): void
    {
        $setting = self::firstOrCreate(
            ['key' => $key],
            [
                'value' => is_array($value) ? json_encode($value) : (string) $value,
                'type' => is_array($value) ? 'json' : (is_bool($value) ? 'boolean' : 'string'),
            ]
        );
        
        if (!$setting->wasRecentlyCreated) {
            $setting->update([
                'value' => is_array($value) ? json_encode($value) : (string) $value,
                'type' => is_array($value) ? 'json' : (is_bool($value) ? 'boolean' : 'string'),
            ]);
        }

        Cache::forget("setting.{$key}");
    }

    public static function getTemplate(string $type): string
    {
        $key = "sms_template_{$type}";
        return self::get($key, '');
    }

    public static function getSendingTime(): string
    {
        return self::get('sms_sending_time', '08:00');
    }

    public static function getJourJSendingTime(): string
    {
        return self::get('sms_jour_j_sending_time', '06:00');
    }

    public static function updateLastSync(): void
    {
        self::set('commcare_last_sync', now()->toIso8601String());
    }

    public static function getLastSync()
    {
        $sync = self::get('commcare_last_sync');
        return $sync ? \Carbon\Carbon::parse($sync) : null;
    }

    // Statut de synchronisation
    public static function setSyncStatus(string $status): void
    {
        self::set('sync_status', $status);
    }

    public static function getSyncStatus(): ?string
    {
        return self::get('sync_status', 'idle');
    }

    // Cast value selon le type
    protected static function castValue($value, string $type)
    {
        return match($type) {
            'json' => json_decode($value, true),
            'boolean' => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            'integer' => (int) $value,
            default => $value,
        };
    }
}