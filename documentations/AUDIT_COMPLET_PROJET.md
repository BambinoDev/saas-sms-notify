# 🎯 AUDIT COMPLET - PROJET SAAS SMS NOTIFY

**Date :** 11 Octobre 2025  
**Repository :** https://github.com/BambinoDev/saas-sms-notify  
**Status :** ✅ **Repo analysé avec succès**

---

## 📊 RÉSUMÉ EXÉCUTIF

### **✅ CE QUI EXISTE DÉJÀ (Très Bien Fait !)**

```
✅ Structure Backend multi-tenant complète
✅ Tables Organizations + Subscriptions + Pivot
✅ Modèles avec relations et méthodes helper
✅ User::firstOrganization() existe ✅
✅ CommCareService complet avec fetchCases() ✅
✅ Middlewares (EnsureSuperAdmin, SetOrganizationContext)
✅ Jobs (SyncCommCareJob, FetchCommCareDataJob)
✅ Architecture SaaS professionnelle
✅ Laravel 12 + Vue 3 + Inertia 2
✅ Docker Compose setup
```

### **❌ CE QUI MANQUE (Pour Onboarding)**

```
❌ Colonnes onboarding dans table organizations
❌ SignupController vide (TODO)
❌ OnboardingController n'existe pas
❌ Middleware OnboardingIncomplete
❌ Logique métier onboarding (6 étapes)
❌ Sauvegarde progressive données onboarding
❌ Validation CommCare API au signup
```

---

## 🗄️ ANALYSE BASE DE DONNÉES

### **Table `organizations` - État Actuel**

```php
// Migration existante : 2025_10_10_100000_create_organizations_table.php

Schema::create('organizations', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('slug')->unique();
    $table->string('domain')->nullable()->unique();
    $table->text('logo_url')->nullable();
    $table->string('primary_color')->default('#3B82F6');
    $table->string('secondary_color')->default('#10B981');
    $table->enum('status', ['active', 'suspended', 'trial', 'cancelled'])->default('trial');
    $table->timestamp('trial_ends_at')->nullable();
    $table->json('settings')->nullable(); // ⭐ Peut stocker config onboarding
    $table->timestamps();
    $table->softDeletes();
});
```

### **❌ Colonnes Manquantes pour Onboarding**

```sql
-- Colonnes à ajouter via nouvelle migration :

-- Étape 2 : Company Info
organization_type (string, nullable) -- hospital, clinic, etc.
sector (string, nullable) -- health, education, etc.
timezone (string, default: 'Africa/Abidjan')
team_size (string, nullable) -- 1-10, 10-50, etc.

-- Étape 3 : CommCare Config
commcare_domain (string, nullable)
commcare_project_name (string, nullable)
commcare_api_key (text, nullable) -- encrypted
commcare_app_id (string, nullable)

-- Étape 4 : Phone Validation
primary_country (string, default: 'CI')
allowed_prefixes (json, nullable) -- ["01","05","07"]
phone_validation_mode (string, default: 'strict') -- strict/flexible
mobile_only (boolean, default: true)
auto_format_e164 (boolean, default: true)

-- Étape 5 : Field Mappings
field_mappings (json, nullable) -- {case_name: "name", ...}

-- Tracking Onboarding
onboarding_completed (boolean, default: false)
onboarding_completed_at (timestamp, nullable)
onboarding_step (integer, default: 1) -- Pour reprendre où on s'est arrêté
```

### **✅ Table `subscriptions` - OK**

```php
// Déjà existante et complète
- organization_id
- plan (trial/starter/pro/enterprise)
- status (trial/active/cancelled/suspended)
- sms_limit, sms_used
- users_limit, structures_limit
- price, stripe_subscription_id
- current_period_start, current_period_end
- trial_ends_at
```

### **✅ Table `organization_user` - OK**

```php
// Pivot table déjà existante
- user_id
- organization_id
- role (owner/admin/manager/user)
- timestamps
```

---

## 📦 ANALYSE MODÈLES

### **✅ Modèle `Organization` - Excellent**

```php
// Fichier : app/Models/Organization.php
// Status : ✅ Très bien fait

Relations existantes :
✅ users() : BelongsToMany (avec pivot role)
✅ subscription() : HasOne (latest)
✅ subscriptions() : HasMany
✅ cases() : HasMany
✅ smsQueue() : HasMany
✅ rules() : HasMany
✅ owners() : BelongsToMany (where role = owner)
✅ admins() : BelongsToMany (where role in owner/admin)

Méthodes helper existantes :
✅ isOnTrial() : bool
✅ hasActiveSubscription() : bool
✅ isActive() : bool
✅ remainingTrialDays() : ?int

Fillable actuel :
- name, slug, domain, logo_url
- primary_color, secondary_color
- status, trial_ends_at, settings

⚠️ À AJOUTER au $fillable après migration :
- organization_type, sector, timezone, team_size
- commcare_domain, commcare_project_name, commcare_api_key, commcare_app_id
- primary_country, allowed_prefixes, phone_validation_mode, mobile_only, auto_format_e164
- field_mappings, onboarding_completed, onboarding_completed_at, onboarding_step
```

### **✅ Modèle `User` - Excellent**

```php
// Fichier : app/Models/User.php
// Status : ✅ Parfait pour onboarding

Relations existantes :
✅ organizations() : BelongsToMany (avec pivot role)
✅ tenants() : BelongsToMany (système tenancy)
✅ currentOrganization() : BelongsTo

Méthodes helper existantes :
✅ firstOrganization() : ?Organization ⭐ PARFAIT !
✅ belongsToOrganization(Organization) : bool
✅ roleInOrganization(Organization) : ?string
✅ isOwnerOfOrganization(Organization) : bool
✅ isAdminOfOrganization(Organization) : bool
✅ canManageOrganization(Organization) : bool

Fillable :
✅ name, email, password, locale, is_superadmin

Casts :
✅ email_verified_at : datetime
✅ password : hashed
✅ is_superadmin : boolean

⭐ RIEN À MODIFIER - Modèle User parfait !
```

### **✅ Modèle `Subscription` - Excellent**

```php
// Fichier : app/Models/Subscription.php
// Status : ✅ Complet et professionnel

Méthodes utiles :
✅ hasReachedSmsLimit() : bool
✅ remainingSmsQuota() : int
✅ smsUsagePercentage() : float
✅ isNearSmsLimit() : bool (>= 80%)
✅ incrementSmsUsage(int)
✅ decrementSmsUsage(int)
✅ resetMonthlyUsage()
✅ isActive() : bool
✅ isOnTrial() : bool
✅ isExpired() : bool
✅ daysUntilRenewal() : ?int
✅ hasReachedUsersLimit() : bool
✅ hasReachedStructuresLimit() : bool

⭐ RIEN À MODIFIER - Modèle Subscription parfait !
```

---

## 🎛️ ANALYSE CONTROLLERS

### **❌ SignupController - VIDE (TODO)**

```php
// Fichier : app/Http/Controllers/SignupController.php
// Status : ❌ TODO - Juste un redirect

public function store(Request $request)
{
    // TODO: Logique de création tenant + user
    // Pour l'instant, on redirige juste
    return redirect()->route('onboarding.welcome');
}

⚠️ À IMPLÉMENTER :
1. Validation formulaire signup
2. Créer User
3. Créer Organization
4. Créer Subscription (trial)
5. Attacher User à Organization (role: owner)
6. Login automatique
7. Redirect /onboarding/welcome
```

### **❌ OnboardingController - N'EXISTE PAS**

```php
⚠️ À CRÉER : app/Http/Controllers/OnboardingController.php

Méthodes requises (6 étapes x 2 méthodes) :
1. welcome() : GET - Afficher intro
2. company() : GET - Afficher formulaire entreprise
3. storeCompany() : POST - Sauvegarder infos entreprise
4. commcare() : GET - Afficher formulaire CommCare
5. storeCommcare() : POST - Valider + Sauvegarder CommCare
6. phone() : GET - Afficher config téléphonie
7. storePhone() : POST - Sauvegarder config téléphonie
8. mapping() : GET - Afficher formulaire mapping
9. storeMapping() : POST - Sauvegarder mapping
10. completion() : GET - Finaliser + Lancer sync
11. syncProgress() : API - Polling progression sync
```

---

## 🔐 ANALYSE MIDDLEWARES

### **✅ EnsureSuperAdmin - Existe**

```php
// Fichier : app/Http/Middleware/EnsureSuperAdmin.php
// Status : ✅ Existe (pour routes /admin/*)
```

### **✅ SetOrganizationContext - Existe**

```php
// Fichier : app/Http/Middleware/SetOrganizationContext.php
// Status : ✅ Existe (pour contexte organisation)
```

### **❌ OnboardingIncomplete - N'EXISTE PAS**

```php
⚠️ À CRÉER : app/Http/Middleware/EnsureOnboardingIncomplete.php

Logique :
1. Vérifier si user a une organization
2. Vérifier si onboarding_completed = false
3. Si onboarding déjà complété → redirect /dashboard
4. Sinon → continuer
```

---

## 🛠️ ANALYSE SERVICES

### **✅ CommCareService - EXISTE ET COMPLET**

```php
// Fichier : app/Services/CommCareService.php
// Status : ✅ Excellent - Service complet

Méthodes existantes :
✅ fetchCases($lastSyncDate, $limit, $offset) : array|null
✅ Authentification Basic Auth
✅ Gestion timeout
✅ Logs détaillés
✅ Gestion erreurs

⚠️ À AJOUTER (pour validation signup) :
public function testConnection(string $domain, string $apiKey): bool
{
    try {
        $response = Http::withHeaders([
            'Authorization' => 'ApiKey ' . $apiKey,
        ])->timeout(10)->get("https://{$domain}.commcarehq.org/a/{$domain}/api/v0.5/case/");
        
        return $response->successful();
    } catch (\Exception $e) {
        return false;
    }
}
```

### **✅ CommCareSyncService - Existe**

```php
// Fichier : app/Services/CommCareSyncService.php
// Status : ✅ Pour synchronisation async
```

---

## 🚀 ANALYSE JOBS

### **✅ SyncCommCareJob - Existe**

```php
// Fichier : app/Jobs/SyncCommCareJob.php
// Status : ✅ Job pour sync async
```

### **✅ FetchCommCareDataJob - Existe**

```php
// Fichier : app/Jobs/FetchCommCareDataJob.php
// Status : ✅ Job pour fetch data
```

### **✅ GenerateSmsJob - Existe**

```php
// Fichier : app/Jobs/GenerateSmsJob.php
// Status : ✅ Job pour génération SMS
```

---

## 📋 ANALYSE ROUTES

### **Routes Actuelles (web.php)**

```php
// Landing page publique
Route::get('/welcome', ...)->name('landing'); ✅

// Signup routes
Route::get('/signup', ...)->name('signup'); ✅
Route::post('/signup', [SignupController::class, 'store'])->name('signup.store'); ⚠️ TODO

// Onboarding routes (temporairement sans auth)
Route::prefix('onboarding')->group(function () {
    Route::get('/welcome', ...)->name('onboarding.welcome'); ✅
    Route::get('/company', ...)->name('onboarding.company'); ✅
    Route::post('/company', ...)->name('onboarding.company.store'); ⚠️ TODO
    Route::get('/commcare', ...)->name('onboarding.commcare'); ✅
    Route::post('/commcare', ...)->name('onboarding.commcare.store'); ⚠️ TODO
    Route::get('/phone', ...)->name('onboarding.phone'); ✅
    Route::post('/phone', ...)->name('onboarding.phone.store'); ⚠️ TODO
    Route::get('/mapping', ...)->name('onboarding.mapping'); ✅
    Route::post('/mapping', ...)->name('onboarding.mapping.store'); ⚠️ TODO
    Route::get('/completion', ...)->name('onboarding.completion'); ✅
});
```

### **⚠️ Routes à Modifier**

```php
// Ajouter middleware auth + onboarding.incomplete
Route::middleware(['auth', 'onboarding.incomplete'])->prefix('onboarding')->group(function () {
    // ... toutes les routes onboarding
});
```

---

## 📦 DÉPENDANCES (composer.json)

### **✅ Packages Installés**

```json
"require": {
    "php": "^8.2", ✅
    "giggsey/libphonenumber-for-php": "^9.0", ✅ Pour validation téléphone
    "inertiajs/inertia-laravel": "^2.0", ✅
    "laravel/framework": "^12.0", ✅
    "laravel/tinker": "^2.10.1", ✅
    "stancl/tenancy": "^3.9" ✅ Multi-tenancy
}
```

**⭐ AUCUNE DÉPENDANCE À AJOUTER - Tout est là !**

---

## 🎯 PLAN D'ACTION - CE QU'IL FAUT FAIRE

### **PHASE 1 : Migration (5 min) ⭐ PRIORITÉ 1**

```bash
Créer migration : 2025_10_11_add_onboarding_columns_to_organizations_table.php

Colonnes à ajouter (16 colonnes) :
✅ organization_type, sector, timezone, team_size
✅ commcare_domain, commcare_project_name, commcare_api_key, commcare_app_id
✅ primary_country, allowed_prefixes, phone_validation_mode, mobile_only, auto_format_e164
✅ field_mappings, onboarding_completed, onboarding_completed_at, onboarding_step
```

### **PHASE 2 : Modèle Organization (2 min)**

```php
Mettre à jour fillable + casts dans app/Models/Organization.php

Ajouter au $fillable les 16 nouvelles colonnes
Ajouter aux $casts :
- allowed_prefixes => 'array'
- field_mappings => 'array'
- onboarding_completed => 'boolean'
- mobile_only => 'boolean'
- auto_format_e164 => 'boolean'
```

### **PHASE 3 : SignupController (10 min) ⭐ PRIORITÉ 1**

```php
Implémenter app/Http/Controllers/SignupController.php

Logique complète :
1. Validation formulaire (name, email, password, organization_name, country)
2. DB::transaction
3. Créer User
4. Créer Organization (avec slug unique)
5. Créer Subscription (trial, 100 SMS, 1 user)
6. Attacher User à Organization (role: owner)
7. Auth::login($user, true)
8. Redirect /onboarding/welcome
```

### **PHASE 4 : OnboardingController (20 min) ⭐ PRIORITÉ 1**

```php
Créer app/Http/Controllers/OnboardingController.php

11 méthodes :
1. welcome() - GET
2. company() - GET + POST
3. commcare() - GET + POST (avec validation API)
4. phone() - GET + POST
5. mapping() - GET + POST
6. completion() - GET (lance SyncCommCareJob)
7. syncProgress() - API (pour polling)
```

### **PHASE 5 : Middleware (3 min)**

```php
Créer app/Http/Middleware/EnsureOnboardingIncomplete.php

Logique :
Si onboarding_completed = true → redirect /dashboard
```

### **PHASE 6 : Méthode testConnection (3 min)**

```php
Ajouter dans app/Services/CommCareService.php

public function testConnection(string $domain, string $apiKey): bool
{
    // Test connexion CommCare API
}
```

### **PHASE 7 : Routes (2 min)**

```php
Mettre à jour routes/web.php

- Ajouter middleware ['auth', 'onboarding.incomplete'] sur /onboarding/*
- Enregistrer middleware dans bootstrap/app.php
```

---

## ⏱️ TEMPS ESTIMÉ TOTAL

```
Migration .................. 5 min
Modèle Organization ......... 2 min
SignupController ........... 10 min
OnboardingController ....... 20 min
Middleware .................. 3 min
testConnection .............. 3 min
Routes ...................... 2 min
Tests ...................... 10 min
────────────────────────────────
TOTAL ...................... 55 min
```

---

## 🎊 CONCLUSION

### **EXCELLENTE BASE DE CODE ! 🏆**

Votre projet est **TRÈS BIEN STRUCTURÉ** :

✅ Architecture multi-tenant professionnelle
✅ Modèles avec relations et helpers complets
✅ Services bien organisés
✅ Jobs pour tâches async
✅ Middlewares existants
✅ Laravel 12 + Vue 3 + Inertia 2
✅ Docker setup complet

### **CE QUI MANQUE : Juste la Logique Onboarding**

❌ 16 colonnes à ajouter dans organizations
❌ SignupController à implémenter
❌ OnboardingController à créer
❌ 1 middleware à ajouter

**EN RÉSUMÉ :** ~55 minutes de travail pour avoir un onboarding complet et fonctionnel !

---

## 🚀 PROCHAINE ÉTAPE

**Je vais maintenant créer les prompts Cursor AI pour implémenter tout ça !**

Voulez-vous :
- **A)** Tous les prompts d'un coup (migration + controllers + middleware)
- **B)** Phase par phase (on teste après chaque phase)
- **C)** Juste la migration d'abord (puis on continue)

**Dis-moi et je génère les prompts !** 🎯✨
