<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\CasesController;
use App\Http\Controllers\Auth\SignupController;
use App\Http\Controllers\SmsController;
use App\Http\Controllers\TemplatesController;
use App\Http\Controllers\RulesController;
use App\Http\Controllers\SmsReplanificationController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\SmsRuleController;
use App\Http\Controllers\WomenController;
use App\Http\Controllers\OrganizationSettingsController;
use App\Http\Controllers\OnboardingController;
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
Route::get('/signup', [SignupController::class, 'create'])->name('signup');
Route::post('/signup', [SignupController::class, 'store'])->name('signup.store');

// ============================================
// ONBOARDING ROUTES
// ============================================

Route::middleware(['auth'])->prefix('onboarding')->name('onboarding.')->group(function () {
    
    // Étape 1 : Welcome
    Route::get('/welcome', [OnboardingController::class, 'welcome'])
        ->name('welcome');
    
    // Étape 2 : Company
    Route::get('/company', [OnboardingController::class, 'company'])
        ->name('company');
    Route::post('/company', [OnboardingController::class, 'storeCompany'])
        ->name('company.store');
    
    // Étape 3 : CommCare
    Route::get('/commcare', [OnboardingController::class, 'commcare'])
        ->name('commcare');
    Route::post('/commcare/test', [OnboardingController::class, 'testCommcare'])
        ->name('commcare.test');
    Route::post('/commcare', [OnboardingController::class, 'storeCommcare'])
        ->name('commcare.store');
    
    // Étape 4 : Mapping
    Route::get('/mapping', [OnboardingController::class, 'mapping'])
        ->name('mapping');
    Route::post('/mapping/types', [OnboardingController::class, 'fetchCaseTypes'])
        ->name('mapping.types');
    Route::post('/mapping/validate', [OnboardingController::class, 'validateCaseType'])
        ->name('mapping.validate');
    Route::post('/mapping/properties', [OnboardingController::class, 'fetchCaseProperties'])
        ->name('mapping.properties');
    Route::post('/mapping', [OnboardingController::class, 'storeMapping'])
        ->name('mapping.store');
    
    // Étape 5 : Completion
    Route::get('/completion', [OnboardingController::class, 'completion'])
        ->name('completion');
});

// Dashboard with real data
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
Route::get('/', [DashboardController::class, 'index'])->name('home');

// Cases CRUD - Routes spécifiques AVANT les routes avec paramètres
Route::get('/cases', [CasesController::class, 'index'])->name('cases.index');
Route::get('/cases/export', [CasesController::class, 'export'])->name('cases.export');

// Synchronisation CommCare (client) - AVANT les routes avec {id}
Route::middleware(['auth'])->prefix('cases')->name('cases.')->group(function () {
    Route::get('/sync', [\App\Http\Controllers\CaseSyncController::class, 'index'])->name('sync');
    Route::post('/sync', [\App\Http\Controllers\CaseSyncController::class, 'sync'])->name('sync.start');
    Route::get('/sync/status', [\App\Http\Controllers\CaseSyncController::class, 'status'])->name('sync.status');
});

// Routes avec paramètres - APRÈS les routes spécifiques
Route::get('/cases/{id}', [CasesController::class, 'show'])->name('cases.show');
Route::post('/cases/{id}/sync', [CasesController::class, 'sync'])->name('cases.sync.single');

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

// Route SMS avec paramètre - APRÈS les routes spécifiques (déjà définie plus haut)

Route::get('/women', [WomenController::class, 'index'])->name('women.index');

Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
Route::post('/settings', [SettingsController::class, 'update'])->name('settings.update');
Route::post('/settings/sync', [SettingsController::class, 'syncCommCare'])->name('settings.sync');

// SMS Templates (nouveau système)
Route::middleware(['auth'])->group(function () {
    Route::resource('templates', App\Http\Controllers\SmsTemplateController::class);
    
    // SMS Rules (nouveau système)
    Route::resource('rules', App\Http\Controllers\SmsRuleController::class);
    Route::post('rules/{rule}/toggle', [App\Http\Controllers\SmsRuleController::class, 'toggle'])
        ->name('rules.toggle');
    Route::post('rules/{rule}/test', [App\Http\Controllers\SmsRuleController::class, 'test'])
        ->name('rules.test');
    
    // Actions avancées (NOUVEAU)
    Route::post('/rules/{rule}/generate', [App\Http\Controllers\SmsRuleController::class, 'generate'])
        ->name('rules.generate');
    
    Route::post('/rules/{rule}/pause', [App\Http\Controllers\SmsRuleController::class, 'pause'])
        ->name('rules.pause');
    
    Route::post('/rules/{rule}/resume', [App\Http\Controllers\SmsRuleController::class, 'resume'])
        ->name('rules.resume');
});

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

// Routes Paramètres (Settings)
Route::middleware(['auth'])->prefix('settings')->name('settings.')->group(function () {
    Route::get('/', [SettingsController::class, 'index'])->name('index');
    Route::get('/general', [SettingsController::class, 'general'])->name('general');
    Route::get('/sync', [SettingsController::class, 'sync'])->name('sync');
    Route::get('/sms', [SettingsController::class, 'sms'])->name('sms');
    Route::post('/sms/update', [SettingsController::class, 'smsUpdate'])->name('sms.update');
    Route::post('/sms/test', [SettingsController::class, 'smsTest'])->name('sms.test');
    Route::get('/mapping', [SettingsController::class, 'mapping'])->name('mapping');
    Route::post('/mapping/update', [SettingsController::class, 'mappingUpdate'])->name('mapping.update');
    Route::get('/sending', [SettingsController::class, 'sending'])->name('sending');
    Route::post('/sending/update', [SettingsController::class, 'sendingUpdate'])->name('sending.update');
});
