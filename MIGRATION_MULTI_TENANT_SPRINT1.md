# Migration Multi-Tenant - Sprint 1 Jour 1-2
## Système CPN SMS → Plateforme SAAS

**Date :** 10 octobre 2025  
**Status :** ✅ Fichiers créés - En attente d'exécution

---

## 📋 Ce qui a été créé

### 1. Migrations (4 fichiers)

✅ **database/migrations/2025_10_10_100000_create_organizations_table.php**
- Table `organizations` avec colonnes : name, slug, domain, logo_url, primary_color, secondary_color, status, trial_ends_at, settings
- Soft deletes activé
- Index sur status et trial_ends_at

✅ **database/migrations/2025_10_10_100001_create_subscriptions_table.php**
- Table `subscriptions` liée à organizations
- Plans : starter, pro, enterprise
- Limites : SMS, users, structures
- Tracking : sms_limit, sms_used, current_period, Stripe integration
- Index sur organization_id, status, current_period_end

✅ **database/migrations/2025_10_10_100002_create_organization_user_table.php**
- Table pivot `organization_user`
- Rôles : owner, admin, manager, user
- Contrainte unique sur (organization_id, user_id)

✅ **database/migrations/2025_10_10_100003_add_organization_to_tenant_tables.php**
- Ajoute colonne `organization_id` aux tables :
  - `women` (cases)
  - `sms_queue`
  - `sms_rules`
- Foreign key avec cascade delete
- Index pour performance

### 2. Modèles (2 nouveaux + 1 modifié)

✅ **app/Models/Organization.php**
- Relations : users, subscription, subscriptions, cases, smsQueue, rules
- Méthodes métier :
  - `isOnTrial()` - Vérifie si en période d'essai
  - `hasActiveSubscription()` - Vérifie subscription active
  - `isActive()` - Statut global
  - `remainingTrialDays()` - Jours restants
  - `owners()` - Récupère les propriétaires
  - `admins()` - Récupère les admins

✅ **app/Models/Subscription.php**
- Relations : organization
- Méthodes métier :
  - `hasReachedSmsLimit()` - Limite SMS atteinte
  - `remainingSmsQuota()` - Quota restant
  - `smsUsagePercentage()` - Pourcentage utilisation
  - `isNearSmsLimit()` - Proche limite (≥80%)
  - `incrementSmsUsage()` - Incrémenter usage
  - `resetMonthlyUsage()` - Reset mensuel
  - `isActive()`, `isOnTrial()`, `isExpired()`
  - `daysUntilRenewal()` - Jours avant renouvellement
  - `hasReachedUsersLimit()` - Limite utilisateurs
  - `hasReachedStructuresLimit()` - Limite structures

✅ **app/Models/User.php** (modifié)
- Nouvelles relations :
  - `organizations()` - Many-to-many avec pivot role
  - `currentOrganization()` - Organisation courante
- Nouvelles méthodes :
  - `belongsToOrganization()` - Vérifie appartenance
  - `roleInOrganization()` - Récupère rôle
  - `isOwnerOfOrganization()` - Vérifie propriétaire
  - `isAdminOfOrganization()` - Vérifie admin
  - `canManageOrganization()` - Permissions gestion
  - `firstOrganization()` - Première org

### 3. Seeder

✅ **database/seeders/DefaultOrganizationSeeder.php**
- Crée organisation par défaut : "Ministère de la Santé - Côte d'Ivoire"
- Slug : `ministere-sante-ci`
- Status : `active`
- Couleurs : Orange (#FF7900) et Vert (#009E60) CI
- Crée subscription Enterprise :
  - Plan : enterprise
  - SMS limit : 999,999
  - Users limit : 999
  - Structures limit : 999
  - Prix : 0 € (gratuit gouvernement)
  - Période : 1 an
- Attache tous les users existants :
  - User ID 1 → owner
  - Autres users → admin
- Migre toutes les données existantes :
  - Cases (women) → organization_id
  - SMS Queue → organization_id
  - SMS Rules → organization_id

---

## 🚀 COMMANDES À EXÉCUTER

### Étape 1 : Démarrer Docker

```bash
# Sur macOS
open -a Docker

# Attendre que Docker soit complètement démarré (icône stable)
```

### Étape 2 : Exécuter les migrations

```bash
docker exec notify_sms_app php artisan migrate
```

**Résultat attendu :**
```
Running migrations.
2025_10_10_100000_create_organizations_table ..................... DONE
2025_10_10_100001_create_subscriptions_table .................... DONE
2025_10_10_100002_create_organization_user_table ................ DONE
2025_10_10_100003_add_organization_to_tenant_tables ............. DONE
```

### Étape 3 : Exécuter le seeder

```bash
docker exec notify_sms_app php artisan db:seed --class=DefaultOrganizationSeeder
```

**Résultat attendu :**
```
🚀 Création de l'organisation par défaut...
✅ Organisation créée : Ministère de la Santé - Côte d'Ivoire
✅ Subscription créée : Plan enterprise
✅ X utilisateurs attachés à l'organisation
✅ X cases migrés
✅ X SMS en queue migrés
✅ X règles SMS migrées

════════════════════════════════════════════════════
✨ MIGRATION MULTI-TENANT RÉUSSIE !
════════════════════════════════════════════════════
```

---

## ✅ VALIDATION

### 1. Vérifier le statut des migrations

```bash
docker exec notify_sms_app php artisan migrate:status
```

Toutes les migrations doivent afficher **Ran**.

### 2. Vérifier l'organisation créée

```bash
docker exec notify_sms_app php artisan tinker --execute="
\$org = \App\Models\Organization::first();
echo 'Organisation: ' . \$org->name . '\n';
echo 'Slug: ' . \$org->slug . '\n';
echo 'Status: ' . \$org->status . '\n';
echo 'Users: ' . \$org->users()->count() . '\n';
echo 'Cases: ' . \$org->cases()->count() . '\n';
echo 'SMS Queue: ' . \$org->smsQueue()->count() . '\n';
echo 'Rules: ' . \$org->rules()->count() . '\n';
"
```

**Résultat attendu :**
```
Organisation: Ministère de la Santé - Côte d'Ivoire
Slug: ministere-sante-ci
Status: active
Users: [nombre d'utilisateurs]
Cases: [nombre de cases]
SMS Queue: [nombre de SMS]
Rules: [nombre de règles]
```

### 3. Vérifier la subscription

```bash
docker exec notify_sms_app php artisan tinker --execute="
\$sub = \App\Models\Subscription::first();
echo 'Plan: ' . \$sub->plan . '\n';
echo 'Status: ' . \$sub->status . '\n';
echo 'SMS Limit: ' . number_format(\$sub->sms_limit) . '\n';
echo 'SMS Used: ' . \$sub->sms_used . '\n';
echo 'Users Limit: ' . \$sub->users_limit . '\n';
echo 'Structures Limit: ' . \$sub->structures_limit . '\n';
echo 'Price: ' . \$sub->price . ' €\n';
"
```

**Résultat attendu :**
```
Plan: enterprise
Status: active
SMS Limit: 999,999
SMS Used: 0
Users Limit: 999
Structures Limit: 999
Price: 0.00 €
```

### 4. Vérifier les users

```bash
docker exec notify_sms_app php artisan tinker --execute="
\$user = \App\Models\User::first();
echo 'User: ' . \$user->name . '\n';
echo 'Organizations: ' . \$user->organizations()->count() . '\n';
\$org = \$user->firstOrganization();
echo 'First Org: ' . \$org->name . '\n';
echo 'Role: ' . \$user->roleInOrganization(\$org) . '\n';
echo 'Is Owner: ' . (\$user->isOwnerOfOrganization(\$org) ? 'Yes' : 'No') . '\n';
echo 'Is Admin: ' . (\$user->isAdminOfOrganization(\$org) ? 'Yes' : 'No') . '\n';
"
```

### 5. Vérifier que toutes les données ont organization_id

```bash
docker exec notify_sms_app php artisan tinker --execute="
echo 'Cases sans org: ' . \App\Models\CaseModel::whereNull('organization_id')->count() . '\n';
echo 'SMS Queue sans org: ' . \App\Models\SmsQueue::whereNull('organization_id')->count() . '\n';
echo 'Rules sans org: ' . \App\Models\SmsRule::whereNull('organization_id')->count() . '\n';
"
```

**Résultat attendu :**
```
Cases sans org: 0
SMS Queue sans org: 0
Rules sans org: 0
```

### 6. Tester l'application

```bash
# Démarrer l'application
docker-compose up -d

# Accéder à l'application
# http://localhost:8000
```

✅ **L'application doit toujours fonctionner normalement**  
✅ **Toutes les données sont maintenant liées à l'organisation par défaut**

---

## 📊 STRUCTURE CRÉÉE

```
┌─────────────────────────────────────────────────────┐
│                 ORGANIZATIONS                       │
│  • id, name, slug, domain, logo_url                │
│  • primary_color, secondary_color                  │
│  • status, trial_ends_at, settings                 │
└──────────────────┬──────────────────────────────────┘
                   │
        ┌──────────┴──────────┬──────────────┐
        │                     │              │
        ▼                     ▼              ▼
┌────────────────┐   ┌────────────────┐   ┌─────────────────┐
│ SUBSCRIPTIONS  │   │ organization_  │   │ USERS           │
│                │   │     user       │   │ (pivot table)   │
│ • plan         │   │ (pivot table)  │   │                 │
│ • sms_limit    │   │ • role         │   │                 │
│ • sms_used     │   └────────────────┘   └─────────────────┘
│ • users_limit  │
│ • price        │
└────────────────┘

        ┌──────────┴──────────────────────────────────┐
        │                                              │
        ▼                      ▼                       ▼
┌────────────────┐   ┌────────────────┐   ┌─────────────────┐
│ WOMEN (cases)  │   │   SMS_QUEUE    │   │   SMS_RULES     │
│ + org_id       │   │   + org_id     │   │   + org_id      │
└────────────────┘   └────────────────┘   └─────────────────┘
```

---

## 🎯 PROCHAINES ÉTAPES (Sprint 1 Jour 3-5)

1. **Middleware OrganizationContext**
   - Détecter automatiquement l'organisation du user
   - Ajouter au request context
   - Filtrer automatiquement les données

2. **Scopes Eloquent**
   - Ajouter `scopeForOrganization()` à tous les modèles
   - Auto-filter par organization_id

3. **Pages Admin - Organizations**
   - Liste des organizations
   - Créer/éditer organization
   - Gérer users et rôles
   - Visualiser subscription

4. **Pages Admin - Subscriptions**
   - Gérer les plans
   - Visualiser l'usage SMS
   - Configurer les limites

5. **Tests unitaires**
   - Tester les modèles
   - Tester les relations
   - Tester les permissions

---

## 📝 NOTES IMPORTANTES

⚠️ **IMPORTANT :**
- La structure actuelle (Tenant) reste intacte et fonctionnelle
- Organizations est une couche SUPPLÉMENTAIRE pour le SAAS
- Aucune donnée n'est perdue
- L'application reste fonctionnelle pendant la migration
- Rollback possible via `php artisan migrate:rollback --step=4`

🔒 **SÉCURITÉ :**
- Toutes les clés étrangères avec cascade delete
- Soft deletes sur organizations
- Validation des rôles (enum)
- Index pour performance

💡 **BONNES PRATIQUES :**
- Type hints stricts (declare(strict_types=1))
- Return types sur toutes les méthodes
- Relations Eloquent typées
- Documentation PHPDoc
- Noms explicites et cohérents

---

**Dernière mise à jour :** 10 octobre 2025, 11:00  
**Version :** 1.0.0  
**Auteur :** Assistant IA Senior Full-Stack Developer

