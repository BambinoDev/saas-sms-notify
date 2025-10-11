<?php

use App\Http\Controllers\Api\CaseController;
use App\Http\Controllers\Api\SmsTemplateController;
use App\Http\Controllers\Api\SmsQueueController;
use Illuminate\Support\Facades\Route;

// Routes nécessitant un tenant
Route::middleware(['tenant'])->group(function () {
    
    // Cases
    Route::prefix('cases')->group(function () {
        Route::get('/', [CaseController::class, 'index']);
        Route::get('/stats', [CaseController::class, 'stats']);
        Route::get('/{id}', [CaseController::class, 'show']);
        Route::post('/', [CaseController::class, 'store']);
        Route::put('/{id}', [CaseController::class, 'update']);
        Route::delete('/{id}', [CaseController::class, 'destroy']);
    });

    // SMS Templates
    Route::prefix('sms-templates')->group(function () {
        Route::get('/', [SmsTemplateController::class, 'index']);
        Route::get('/stats', [SmsTemplateController::class, 'stats']);
        Route::get('/{id}', [SmsTemplateController::class, 'show']);
        Route::post('/', [SmsTemplateController::class, 'store']);
        Route::put('/{id}', [SmsTemplateController::class, 'update']);
        Route::delete('/{id}', [SmsTemplateController::class, 'destroy']);
        Route::post('/{id}/test-render', [SmsTemplateController::class, 'testRender']);
    });

    // SMS Queue
    Route::prefix('sms-queue')->group(function () {
        Route::get('/', [SmsQueueController::class, 'index']);
        Route::get('/stats', [SmsQueueController::class, 'stats']);
        Route::get('/{id}', [SmsQueueController::class, 'show']);
        Route::post('/{id}/retry', [SmsQueueController::class, 'retry']);
        Route::post('/{id}/cancel', [SmsQueueController::class, 'cancel']);
    });
});

// Note: Les routes SMS pour Flutter seront réimplémentées plus tard
// si nécessaire avec la nouvelle architecture multi-tenant

// Route de santé (sans auth)
Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'timestamp' => now()->toIso8601String(),
    ]);
});
