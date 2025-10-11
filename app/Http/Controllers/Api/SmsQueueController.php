<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SmsQueue;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class SmsQueueController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = SmsQueue::with(['case', 'template']);

        // Filtres
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('from_date')) {
            $query->where('scheduled_at', '>=', $request->from_date);
        }

        if ($request->has('to_date')) {
            $query->where('scheduled_at', '<=', $request->to_date);
        }

        if ($request->has('case_id')) {
            $query->where('case_id', $request->case_id);
        }

        $sms = $query->orderBy('scheduled_at', 'desc')
            ->paginate($request->get('per_page', 15));

        return response()->json($sms);
    }

    public function show(string $id): JsonResponse
    {
        $sms = SmsQueue::with(['case', 'template', 'deliveryReports'])->findOrFail($id);
        return response()->json($sms);
    }

    public function stats(): JsonResponse
    {
        return response()->json([
            'total' => SmsQueue::count(),
            'pending' => SmsQueue::where('status', 'pending')->count(),
            'sent' => SmsQueue::where('status', 'sent')->count(),
            'delivered' => SmsQueue::where('status', 'delivered')->count(),
            'failed' => SmsQueue::where('status', 'failed')->count(),
            'ready_to_send' => SmsQueue::readyToSend()->count(),
            'by_status' => SmsQueue::selectRaw('status, count(*) as count')
                ->groupBy('status')
                ->get(),
            'success_rate' => $this->calculateSuccessRate(),
        ]);
    }

    private function calculateSuccessRate(): float
    {
        $total = SmsQueue::whereIn('status', ['sent', 'delivered', 'failed'])->count();
        if ($total === 0) return 0;
        
        $success = SmsQueue::whereIn('status', ['sent', 'delivered'])->count();
        return round(($success / $total) * 100, 2);
    }

    public function retry(string $id): JsonResponse
    {
        $sms = SmsQueue::findOrFail($id);

        if ($sms->status !== 'failed') {
            return response()->json([
                'error' => 'Invalid status',
                'message' => 'Only failed SMS can be retried'
            ], 400);
        }

        if (!$sms->canRetry()) {
            return response()->json([
                'error' => 'Max retries exceeded',
                'message' => 'This SMS has exceeded the maximum number of retries'
            ], 400);
        }

        // Reset status to pending for retry
        $sms->update([
            'status' => 'pending',
            'retry_count' => $sms->retry_count + 1,
            'last_retry_at' => now(),
            'error_code' => null,
            'error_message' => null,
        ]);

        return response()->json([
            'message' => 'SMS queued for retry',
            'retry_count' => $sms->retry_count
        ]);
    }

    public function cancel(string $id): JsonResponse
    {
        $sms = SmsQueue::findOrFail($id);

        if (!in_array($sms->status, ['pending', 'queued'])) {
            return response()->json([
                'error' => 'Cannot cancel',
                'message' => 'Only pending or queued SMS can be cancelled'
            ], 400);
        }

        $sms->update(['status' => 'cancelled']);

        return response()->json(['message' => 'SMS cancelled successfully']);
    }
}
