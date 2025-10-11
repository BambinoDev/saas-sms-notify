<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\CasesController;
use App\Http\Controllers\SignupController;
use App\Http\Controllers\SmsController;
use App\Http\Controllers\TemplatesController;
use App\Http\Controllers\RulesController;
use App\Http\Controllers\SmsReplanificationController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\SmsRuleController;
use App\Http\Controllers\WomenController;
use App\Http\Controllers\OrganizationSettingsController;
use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminOrganizationsController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// Auth routes (publiques)
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Landing page publique
Route::get('/welcome', function () {
    return Inertia::render('Landing');
})->name('landing');

// Signup routes
Route::get('/signup', function () {
    return Inertia::render('Auth/Signup');
})->name('signup');

Route::post('/signup', [SignupController::class, 'store'])->name('signup.store');

// Onboarding routes (temporairement sans auth pour le MVP)
Route::prefix('onboarding')->group(function () {
    Route::get('/welcome', function () {
        return Inertia::render('Onboarding/Welcome');
    })->name('onboarding.welcome');

    Route::get('/company', function () {
        return Inertia::render('Onboarding/Company');
    })->name('onboarding.company');

    Route::post('/company', function () {
        // TODO: Sauvegarder les données
        return redirect()->route('onboarding.commcare');
    })->name('onboarding.company.store');

    Route::get('/commcare', function () {
        return Inertia::render('Onboarding/CommCare');
    })->name('onboarding.commcare');

    Route::post('/commcare', function () {
        // TODO: Sauvegarder et valider les credentials
        return redirect()->route('onboarding.phone');
    })->name('onboarding.commcare.store');

    Route::get('/phone', function () {
        return Inertia::render('Onboarding/Phone');
    })->name('onboarding.phone');

    Route::post('/phone', function () {
        // TODO: Sauvegarder la config téléphonie
        return redirect()->route('onboarding.mapping');
    })->name('onboarding.phone.store');

    Route::get('/mapping', function () {
        return Inertia::render('Onboarding/Mapping');
    })->name('onboarding.mapping');

    Route::post('/mapping', function () {
        // TODO: Sauvegarder les mappings
        return redirect()->route('onboarding.completion');
    })->name('onboarding.mapping.store');

    Route::get('/completion', function () {
        return Inertia::render('Onboarding/Completion');
    })->name('onboarding.completion');

    // TODO: Ajouter les autres étapes
});

// Dashboard with real data
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
Route::get('/', [DashboardController::class, 'index'])->name('home');

// Cases CRUD
Route::get('/cases', [CasesController::class, 'index'])->name('cases.index');
Route::get('/cases/export', [CasesController::class, 'export'])->name('cases.export');
Route::get('/cases/{id}', [CasesController::class, 'show'])->name('cases.show');
Route::post('/cases/{id}/sync', [CasesController::class, 'sync'])->name('cases.sync');

// SMS Queue (avec backend)
Route::get('/sms', [SmsController::class, 'index'])->name('sms.index');
Route::get('/sms/export', [SmsController::class, 'export'])->name('sms.export');
Route::post('/sms/retry-all-failed', [SmsController::class, 'retryAllFailed'])->name('sms.retry-all-failed');
Route::get('/sms/{id}', [SmsController::class, 'show'])->name('sms.show');
Route::post('/sms/{id}/retry', [SmsController::class, 'retry'])->name('sms.retry');
Route::post('/sms/{id}/send-now', [SmsController::class, 'sendNow'])->name('sms.send-now');
Route::post('/sms/bulk-retry', [SmsController::class, 'bulkRetry'])->name('sms.bulk-retry');
Route::delete('/sms/{id}', [SmsController::class, 'destroy'])->name('sms.destroy');

// Routes Replanification SMS - Routes spécifiques AVANT les routes avec paramètres
Route::get('/sms/replanification', [SmsReplanificationController::class, 'index'])->name('sms.replanification');
Route::post('/sms/replanification', [SmsReplanificationController::class, 'replanify'])->name('sms.replanify');
Route::post('/sms/replanification/template', [SmsReplanificationController::class, 'replanifyByTemplate'])->name('sms.replanify.template');
Route::get('/sms/replanification/stats', [SmsReplanificationController::class, 'stats'])->name('sms.replanification.stats');

// Route SMS avec paramètre - APRÈS les routes spécifiques
Route::get('/sms/{id}', [SmsController::class, 'show'])->name('sms.show');

Route::get('/women', [WomenController::class, 'index'])->name('women.index');

Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
Route::post('/settings', [SettingsController::class, 'update'])->name('settings.update');
Route::post('/settings/sync', [SettingsController::class, 'syncCommCare'])->name('settings.sync');

// Templates (avec backend)
Route::get('/templates', [TemplatesController::class, 'index'])->name('templates.index');
Route::post('/templates', [TemplatesController::class, 'store'])->name('templates.store');
Route::put('/templates/{id}', [TemplatesController::class, 'update'])->name('templates.update');
Route::delete('/templates/{id}', [TemplatesController::class, 'destroy'])->name('templates.destroy');
Route::post('/templates/{id}/duplicate', [TemplatesController::class, 'duplicate'])->name('templates.duplicate');
Route::post('/templates/preview', [TemplatesController::class, 'preview'])->name('templates.preview');
Route::post('/templates/{id}/toggle', [TemplatesController::class, 'toggleStatus'])->name('templates.toggle');

// Rules (avec backend complet)
Route::get('/rules', [RulesController::class, 'index'])->name('rules.index');
Route::post('/rules', [RulesController::class, 'store'])->name('rules.store');
Route::put('/rules/{id}', [RulesController::class, 'update'])->name('rules.update');
Route::delete('/rules/{id}', [RulesController::class, 'destroy'])->name('rules.destroy');
Route::post('/rules/{id}/toggle', [RulesController::class, 'toggleStatus'])->name('rules.toggle');
Route::post('/rules/priority', [RulesController::class, 'updatePriority'])->name('rules.priority');

// Routes SMS Rules
Route::get('/settings/sms-rules', [SmsRuleController::class, 'index'])->name('sms-rules.index');
Route::post('/settings/sms-rules', [SmsRuleController::class, 'store'])->name('sms-rules.store');
Route::post('/settings/sms-rules/{rule}/update', [SmsRuleController::class, 'update'])->name('sms-rules.update');
Route::delete('/settings/sms-rules/{rule}', [SmsRuleController::class, 'destroy'])->name('sms-rules.destroy');
Route::post('/settings/sms-rules/{rule}/toggle', [SmsRuleController::class, 'toggle'])->name('sms-rules.toggle');

// ============================================
// Organization Settings (CLIENT - sa propre org)
// ============================================
Route::middleware(['auth'])->prefix('organization/settings')->name('organization.settings.')->group(function () {
    Route::get('/', [OrganizationSettingsController::class, 'index'])->name('index');
    Route::get('/general', [OrganizationSettingsController::class, 'general'])->name('general');
    Route::put('/general', [OrganizationSettingsController::class, 'updateGeneral'])->name('update-general');
    Route::get('/members', [OrganizationSettingsController::class, 'members'])->name('members');
    Route::post('/members', [OrganizationSettingsController::class, 'inviteMember'])->name('invite-member');
    Route::put('/members/{user}/role', [OrganizationSettingsController::class, 'updateMemberRole'])->name('update-member-role');
    Route::delete('/members/{user}', [OrganizationSettingsController::class, 'removeMember'])->name('remove-member');
    Route::get('/billing', [OrganizationSettingsController::class, 'billing'])->name('billing');
    Route::get('/api', [OrganizationSettingsController::class, 'api'])->name('api');
});

// ============================================
// ADMIN - Auth (publique pour admin)
// ============================================
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/login', [AdminAuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AdminAuthController::class, 'login'])->name('login.post');
});

// ============================================
// ADMIN - Organizations Management (SUPERADMIN)
// ============================================
Route::middleware(['auth', 'superadmin'])->prefix('admin')->name('admin.')->group(function () {
    // Logout admin
    Route::post('/logout', [AdminAuthController::class, 'logout'])->name('logout');
    
    // Dashboard admin
    Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');
    
    // Gestion organizations
    Route::get('/organizations', [AdminOrganizationsController::class, 'index'])->name('organizations.index');
    Route::get('/organizations/create', [AdminOrganizationsController::class, 'create'])->name('organizations.create');
    Route::post('/organizations', [AdminOrganizationsController::class, 'store'])->name('organizations.store');
    Route::get('/organizations/{organization}', [AdminOrganizationsController::class, 'show'])->name('organizations.show');
    Route::get('/organizations/{organization}/edit', [AdminOrganizationsController::class, 'edit'])->name('organizations.edit');
    Route::put('/organizations/{organization}', [AdminOrganizationsController::class, 'update'])->name('organizations.update');
    Route::delete('/organizations/{organization}', [AdminOrganizationsController::class, 'destroy'])->name('organizations.destroy');
    
    // Members management
    Route::get('/organizations/{organization}/members', [AdminOrganizationsController::class, 'members'])->name('organizations.members');
    Route::post('/organizations/{organization}/members', [AdminOrganizationsController::class, 'inviteMember'])->name('organizations.invite-member');
    Route::put('/organizations/{organization}/members/{user}/role', [AdminOrganizationsController::class, 'updateMemberRole'])->name('organizations.update-member-role');
    Route::delete('/organizations/{organization}/members/{user}', [AdminOrganizationsController::class, 'removeMember'])->name('organizations.remove-member');
});
