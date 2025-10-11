<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SmsLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'sms_queue_id',
        'woman_id',
        'phone_number',
        'message_content',
        'sms_type',
        'appointment_date',
        'status',
        'gateway_response',
        'error_details',
        'sent_at',
        'delivered_at',
    ];

    protected $casts = [
        'appointment_date' => 'date',
        'sent_at' => 'datetime',
        'delivered_at' => 'datetime',
    ];

    // Relations
    public function smsQueue()
    {
        return $this->belongsTo(SmsQueue::class, 'sms_queue_id');
    }

    public function woman()
    {
        return $this->belongsTo(Woman::class);
    }

    // Scopes
    public function scopeSuccessful($query)
    {
        return $query->whereIn('status', ['sent', 'delivered']);
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    public function scopeForDate($query, $date)
    {
        return $query->whereDate('appointment_date', $date);
    }

    public function scopeRecent($query, int $days = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    // Méthodes statiques pour stats
    public static function statsForPeriod($startDate, $endDate)
    {
        return self::whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('
                COUNT(*) as total,
                SUM(CASE WHEN status IN (\'sent\', \'delivered\') THEN 1 ELSE 0 END) as successful,
                SUM(CASE WHEN status = \'failed\' THEN 1 ELSE 0 END) as failed,
                SUM(CASE WHEN status = \'delivered\' THEN 1 ELSE 0 END) as delivered
            ')
            ->first();
    }
}