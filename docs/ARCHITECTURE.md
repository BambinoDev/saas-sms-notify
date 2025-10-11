# CPN SMS Reminder - Documentation Technique Complète

## 📋 Vue d'ensemble du projet

**CPN SMS Reminder** est un système automatisé d'envoi de SMS de rappel pour les consultations prénatales (CPN) en Côte d'Ivoire. Le système synchronise les données depuis CommCare, génère automatiquement des SMS de rappel, et les envoie via une API Flutter.

### 🎯 Objectifs principaux
- Synchronisation automatique des données femmes depuis CommCare
- Génération automatique de SMS de rappel selon des règles configurables
- Envoi des SMS via une application Flutter mobile
- Gestion des fenêtres de rattrapage configurables
- Interface d'administration web complète

---

## 🏗️ Architecture Technique

### Stack Technologique
- **Backend** : Laravel 12.x, PHP 8.2+, PostgreSQL 17.6, Redis 7.4
- **Frontend** : Vue.js 3.5.x, Inertia.js 2.x, TailwindCSS 3.4.x
- **Mobile** : Flutter 3.35.x (en développement)
- **Infrastructure** : Docker Compose, Nginx, pgAdmin, MailHog
- **Queue** : Laravel Database Queue
- **Scheduler** : Laravel Task Scheduler

### Structure des Conteneurs Docker
```yaml
services:
  - app (Laravel)
  - nginx (Serveur web)
  - postgres (Base de données)
  - redis (Cache et sessions)
  - node (Build frontend)
  - pgadmin (Administration DB)
  - mailhog (Test emails)
  - scheduler (Tâches planifiées)
  - worker (Traitement des queues)
```

---

## 🗄️ Architecture de Base de Données

### Table `women` - Données des femmes enceintes
```sql
CREATE TABLE women (
    id BIGSERIAL PRIMARY KEY,
    case_id VARCHAR(255) UNIQUE NOT NULL,
    case_name VARCHAR(255),
    client_age INTEGER,
    contact_phone_number VARCHAR(20), -- Format: +225XXXXXXXXXX
    husband_phone_number VARCHAR(20), -- Format: +225XXXXXXXXXX
    contact_phone_number_is_verified BOOLEAN DEFAULT false,
    consent_sms_yes BOOLEAN DEFAULT false,
    anc_counter INTEGER DEFAULT 0,
    next_visit_date TIMESTAMP,
    two_days_before_next_visit_date TIMESTAMP,
    structure_sanitaire VARCHAR(255),
    district_sanitaire VARCHAR(255),
    region_sanitaire VARCHAR(255),
    raw_properties JSONB,
    closed BOOLEAN DEFAULT false,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

**Index recommandés :**
- `idx_women_case_id` sur `case_id`
- `idx_women_next_visit_date` sur `next_visit_date`
- `idx_women_eligible` sur `(consent_sms_yes, contact_phone_number_is_verified, closed)`

### Table `sms_rules` - Règles de génération SMS
```sql
CREATE TABLE sms_rules (
    id BIGSERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    type VARCHAR(50) UNIQUE NOT NULL, -- j-2, jour-j, etc.
    days_before INTEGER NOT NULL, -- 0=Jour-J, 2=J-2, etc.
    sending_time TIME NOT NULL, -- Heure d'envoi prévue
    template TEXT NOT NULL, -- Template du message
    active BOOLEAN DEFAULT true,
    priority INTEGER DEFAULT 0,
    window_start TIME DEFAULT '06:00', -- Début fenêtre rattrapage
    window_end TIME DEFAULT '12:00', -- Fin fenêtre rattrapage
    window_enabled BOOLEAN DEFAULT true, -- Activer fenêtre
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

### Table `sms_queue` - File d'attente des SMS
```sql
CREATE TABLE sms_queue (
    id BIGSERIAL PRIMARY KEY,
    woman_id BIGINT REFERENCES women(id),
    recipient_phone VARCHAR(20) NOT NULL,
    message_content TEXT NOT NULL,
    sms_type VARCHAR(50) NOT NULL, -- Référence à sms_rules.type
    status VARCHAR(20) DEFAULT 'pending', -- pending, sent, delivered, failed
    scheduled_at TIMESTAMP NOT NULL,
    sent_at TIMESTAMP NULL,
    delivered_at TIMESTAMP NULL,
    error_message TEXT NULL,
    retry_count INTEGER DEFAULT 0,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

**Index recommandés :**
- `idx_sms_queue_status` sur `status`
- `idx_sms_queue_scheduled_at` sur `scheduled_at`
- `idx_sms_queue_type` sur `sms_type`

### Table `sms_logs` - Historique des SMS
```sql
CREATE TABLE sms_logs (
    id BIGSERIAL PRIMARY KEY,
    sms_queue_id BIGINT REFERENCES sms_queue(id),
    sms_rule_id BIGINT REFERENCES sms_rules(id),
    woman_id BIGINT REFERENCES women(id),
    action VARCHAR(50) NOT NULL, -- generated, sent, delivered, failed
    details JSONB,
    created_at TIMESTAMP
);
```

### Table `app_settings` - Configuration système
```sql
CREATE TABLE app_settings (
    id BIGSERIAL PRIMARY KEY,
    key VARCHAR(255) UNIQUE NOT NULL,
    value TEXT,
    description TEXT,
    updated_at TIMESTAMP
);
```

---

## 🔄 Flux de Données et Processus

### 1. Synchronisation CommCare (02:30 UTC)
```php
// Job: FetchCommCareDataJob
// Fréquence: Quotidienne à 02:30 (Africa/Abidjan)
// Processus:
1. Récupération des cas depuis l'API CommCare
2. Validation et formatage des numéros de téléphone
3. Mise à jour/création des enregistrements women
4. Marquage des dossiers fermés
5. Logs détaillés des statistiques
```

### 2. Génération SMS (05:00 UTC)
```php
// Job: GenerateSmsJob
// Fréquence: Quotidienne à 05:00 (Africa/Abidjan)
// Processus:
1. Récupération des règles SMS actives
2. Identification des femmes éligibles par règle
3. Génération des SMS dans sms_queue
4. Calcul des statistiques par type
5. Logs de génération
```

### 3. Envoi SMS (via API Flutter)
```php
// Endpoint: /api/sms/pending
// Processus:
1. Vérification des fenêtres de rattrapage par règle
2. Filtrage des SMS selon les fenêtres actives
3. Retour des SMS éligibles à l'app Flutter
4. Logs détaillés du filtrage
```

### 4. Mise à jour statuts SMS
```php
// Endpoints: /api/sms/{id}/sent, /delivered, /failed
// Processus:
1. Mise à jour du statut dans sms_queue
2. Enregistrement dans sms_logs
3. Gestion des tentatives de retry
```

---

## 🧩 Modèles Eloquent

### Modèle `Woman`
```php
class Woman extends Model
{
    protected $fillable = [
        'case_id', 'case_name', 'client_age', 'contact_phone_number',
        'husband_phone_number', 'contact_phone_number_is_verified',
        'consent_sms_yes', 'anc_counter', 'next_visit_date',
        'two_days_before_next_visit_date', 'structure_sanitaire',
        'district_sanitaire', 'region_sanitaire', 'raw_properties', 'closed'
    ];

    protected $casts = [
        'next_visit_date' => 'datetime',
        'two_days_before_next_visit_date' => 'datetime',
        'contact_phone_number_is_verified' => 'boolean',
        'consent_sms_yes' => 'boolean',
        'closed' => 'boolean',
        'raw_properties' => 'array'
    ];

    // Scopes
    public function scopeEligibleForSms($query)
    {
        return $query->where('consent_sms_yes', true)
                    ->where('contact_phone_number_is_verified', true)
                    ->whereNotNull('contact_phone_number')
                    ->where('closed', false);
    }

    public function scopeHasAppointmentOn($query, $date)
    {
        return $query->whereDate('next_visit_date', $date);
    }

    // Relations
    public function smsQueue()
    {
        return $this->hasMany(SmsQueue::class);
    }

    public function smsLogs()
    {
        return $this->hasMany(SmsLog::class);
    }
}
```

### Modèle `SmsRule`
```php
class SmsRule extends Model
{
    protected $fillable = [
        'name', 'type', 'days_before', 'sending_time', 'template',
        'active', 'priority', 'window_start', 'window_end', 'window_enabled'
    ];

    protected $casts = [
        'active' => 'boolean',
        'window_enabled' => 'boolean',
        'days_before' => 'integer',
        'priority' => 'integer'
    ];

    // Méthode clé pour les fenêtres de rattrapage
    public function isInCatchupWindow(): bool
    {
        if (!$this->window_enabled) {
            return true; // Si fenêtre désactivée, toujours disponible
        }
        
        $now = Carbon::now()->format('H:i:s');
        return $now >= $this->window_start && $now <= $this->window_end;
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('priority', 'asc');
    }

    // Relations
    public function smsLogs()
    {
        return $this->hasMany(SmsLog::class);
    }
}
```

### Modèle `SmsQueue`
```php
class SmsQueue extends Model
{
    protected $fillable = [
        'woman_id', 'recipient_phone', 'message_content', 'sms_type',
        'status', 'scheduled_at', 'sent_at', 'delivered_at',
        'error_message', 'retry_count'
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'sent_at' => 'datetime',
        'delivered_at' => 'datetime',
        'retry_count' => 'integer'
    ];

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeSent($query)
    {
        return $query->where('status', 'sent');
    }

    public function scopeDelivered($query)
    {
        return $query->where('status', 'delivered');
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    public function scopeForToday($query)
    {
        return $query->whereDate('scheduled_at', today());
    }

    // Relations
    public function woman()
    {
        return $this->belongsTo(Woman::class);
    }

    public function logs()
    {
        return $this->hasMany(SmsLog::class);
    }
}
```

---

## 🔧 Services et Jobs

### Service `CommCareService`
```php
class CommCareService
{
    // Synchronisation complète des femmes
    public function syncWomen(): array
    
    // Test de synchronisation avec limite
    public function testSync(int $maxRecords = 100): array
    
    // Marquage des dossiers fermés
    public function markClosedCases(): array
    
    // Récupération des données depuis l'API CommCare
    private function fetchWomen(int $offset = 0, int $limit = 1000): array
    
    // Synchronisation d'une femme individuelle
    private function syncWoman(array $caseData): string
}
```

### Service `SmsService`
```php
class SmsService
{
    // Génération des SMS pour une date donnée
    public function generateSmsForDate(Carbon $date): array
    
    // Génération des SMS pour une règle spécifique
    private function generateSmsForRule(SmsRule $rule, Carbon $date): array
    
    // Création d'un SMS individuel
    private function createSms(Woman $woman, SmsRule $rule, Carbon $scheduledAt): SmsQueue
}
```

### Job `FetchCommCareDataJob`
```php
class FetchCommCareDataJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(CommCareService $commCareService): void
    {
        // Exécution de la synchronisation CommCare
        // Logs détaillés des résultats
    }
}
```

### Job `GenerateSmsJob`
```php
class GenerateSmsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        private Carbon $date
    ) {}

    public function handle(SmsService $smsService): void
    {
        // Génération des SMS pour la date spécifiée
        // Calcul des statistiques
        // Logs de génération
    }
}
```

---

## 🌐 API Endpoints

### Endpoints SMS (sans authentification)
```php
// Base URL: /api/sms

GET /pending
// Récupère les SMS en attente selon les fenêtres de rattrapage
// Retourne: { success: true, data: [], total: 0, current_time: "H:i:s" }

POST /{id}/sent
// Marque un SMS comme envoyé
// Body: { delivered_at: "timestamp" }

POST /{id}/delivered  
// Marque un SMS comme livré
// Body: { delivered_at: "timestamp" }

POST /{id}/failed
// Marque un SMS comme échoué
// Body: { error_message: "string" }

POST /{id}/retry
// Relance un SMS échoué
// Body: { retry_count: integer }

GET /stats
// Statistiques générales des SMS
// Retourne: { success: true, stats: {} }

GET /sync-schedule
// Configuration des heures de synchronisation
// Retourne: { success: true, sync_times: [], rules: [] }
```

### Endpoints Dashboard (avec authentification)
```php
// Routes web protégées par auth

GET /sms
// Interface de gestion des SMS
// Fonctionnalités: filtres, sélection multiple, génération manuelle

POST /sms/destroy-multiple
// Suppression multiple de SMS en attente
// Body: { ids: [1, 2, 3] }

POST /sms/generate-manual
// Génération manuelle des SMS du jour

GET /sms/replanification
// Interface de replanification des SMS

POST /sms/replanification
// Replanification des SMS sélectionnés

GET /settings/sms-rules
// Interface de gestion des règles SMS

POST /settings/sms-rules
// Création d'une nouvelle règle SMS

PUT /settings/sms-rules/{id}
// Modification d'une règle SMS
```

---

## 🎨 Interface Utilisateur

### Pages Vue.js

#### `resources/js/Pages/Sms/Index.vue`
- **Fonctionnalités** :
  - Liste des SMS avec filtres dynamiques
  - Sélection multiple avec checkboxes
  - Suppression en lot
  - Génération manuelle des SMS
  - Statistiques en temps réel
  - Pagination

#### `resources/js/Pages/Sms/Replanification.vue`
- **Fonctionnalités** :
  - Interface de replanification des SMS
  - Filtres par statut, type, date
  - Sélection multiple
  - Options de replanification (décalage ou heure spécifique)

#### `resources/js/Pages/Settings/SmsRules.vue`
- **Fonctionnalités** :
  - Gestion des règles SMS
  - Configuration des fenêtres de rattrapage
  - Templates de messages
  - Priorités et statuts
  - Interface modale pour création/édition

### Composants réutilisables
- `AppLayout.vue` : Layout principal avec navigation
- Navigation avec sous-menus pour SMS
- Messages flash pour feedback utilisateur
- Modales pour formulaires

---

## ⚙️ Configuration et Environnement

### Variables d'environnement critiques
```env
# Base de données
DB_CONNECTION=pgsql
DB_HOST=postgres
DB_PORT=5432
DB_DATABASE=cpn_sms
DB_USERNAME=postgres
DB_PASSWORD=password

# Redis
REDIS_HOST=redis
REDIS_PASSWORD=null
REDIS_PORT=6379

# CommCare API
COMMCARE_API_URL=https://www.commcarehq.org/a/domain/api/v0.5/case
COMMCARE_EMAIL=email@domain.com
COMMCARE_API_KEY=api_key

# Application
APP_TIMEZONE=Africa/Abidjan
APP_LOCALE=fr
```

### Configuration Laravel
```php
// config/app.php
'timezone' => 'Africa/Abidjan',

// config/queue.php
'default' => 'database',
'connections' => [
    'database' => [
        'driver' => 'database',
        'table' => 'jobs',
        'queue' => 'default',
        'retry_after' => 90,
    ],
],

// config/schedule.php
Schedule::job(new FetchCommCareDataJob())
    ->dailyAt('02:30')
    ->timezone('Africa/Abidjan');

Schedule::job(new GenerateSmsJob())
    ->dailyAt('05:00')
    ->timezone('Africa/Abidjan');
```

---

## 🔍 Fonctionnalités Avancées

### 1. Fenêtres de Rattrapage Configurables
- **Principe** : Chaque règle SMS peut avoir sa propre fenêtre horaire
- **Configuration** : Interface Dashboard avec toggle et champs time
- **Fonctionnement** : L'API filtre automatiquement selon les fenêtres actives
- **Logs** : Traçabilité complète des règles hors fenêtre

### 2. Validation des Numéros de Téléphone
- **Classe** : `PhoneNumberFormatter`
- **Format** : +225XXXXXXXXXX (Côte d'Ivoire)
- **Validation** : Préfixes 01, 05, 07 uniquement
- **Nettoyage** : Commande `php artisan phone:clean`

### 3. Replanification des SMS
- **Interface** : Page dédiée avec filtres avancés
- **Options** : Décalage temporel ou heure spécifique
- **Cible** : SMS en attente ou échoués uniquement
- **Logs** : Traçabilité des replanifications

### 4. Génération Manuelle
- **Trigger** : Bouton dans le Dashboard SMS
- **Processus** : Dispatch du job `GenerateSmsJob`
- **Feedback** : Messages de succès/erreur
- **Logs** : Traçabilité avec utilisateur et IP

---

## 📊 Monitoring et Logs

### Logs critiques à surveiller
```bash
# Synchronisation CommCare
grep "SYNC COMPLÈTE" storage/logs/laravel.log

# Génération SMS
grep "GÉNÉRATION SMS" storage/logs/laravel.log

# Fenêtres de rattrapage
grep "hors fenêtre de rattrapage" storage/logs/laravel.log

# Erreurs API
grep "Erreur API" storage/logs/laravel.log
```

### Commandes de monitoring
```bash
# Statut des migrations
php artisan migrate:status

# Liste des tâches planifiées
php artisan schedule:list

# Queue des jobs
php artisan queue:work --once

# Statistiques SMS
php artisan tinker
>>> App\Models\SmsQueue::count()
>>> App\Models\SmsQueue::pending()->count()
```

---

## 🚀 Déploiement et Maintenance

### Commandes Docker essentielles
```bash
# Démarrer l'environnement
docker-compose up -d

# Redémarrer un service
docker-compose restart app

# Voir les logs
docker logs cpn_app --tail 100

# Accès au conteneur
docker exec -it cpn_app bash
```

### Commandes Laravel de maintenance
```bash
# Nettoyer les numéros de téléphone
php artisan phone:clean

# Synchronisation manuelle
php artisan commcare:sync

# Génération manuelle SMS
php artisan sms:generate

# Nettoyer les caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
```

---

## 🔮 Prochaines Étapes et Améliorations

### Fonctionnalités en cours de développement
1. **Application Flutter** : Interface mobile pour l'envoi des SMS
2. **Dashboard Analytics** : Graphiques et métriques avancées
3. **Notifications Push** : Alertes pour les administrateurs
4. **API Webhooks** : Intégration avec systèmes externes

### Améliorations techniques recommandées
1. **Tests automatisés** : Suite de tests PHPUnit et Cypress
2. **Monitoring avancé** : Intégration avec outils de monitoring
3. **Backup automatisé** : Sauvegarde régulière de la base de données
4. **Optimisation performance** : Index supplémentaires et requêtes optimisées

### Sécurité
1. **Authentification API** : Tokens pour l'API Flutter
2. **Rate limiting** : Limitation des requêtes API
3. **Audit logs** : Traçabilité des actions utilisateurs
4. **Chiffrement** : Données sensibles chiffrées

---

## 📞 Support et Contact

### Équipe de développement
- **Tech Lead** : Configuration Ultime - Septembre 2025
- **Stack** : Laravel 12, Vue.js 3, Inertia.js 2, PostgreSQL 17, Flutter 3.35

### Documentation technique
- **Standards** : Suivre les règles définies dans `.cursorrules`
- **Architecture** : Respecter les patterns Laravel et Vue.js
- **Sécurité** : Validation systématique des entrées utilisateur
- **Performance** : Optimisation des requêtes et utilisation du cache

---

*Document généré le 4 octobre 2025 - Version 1.0*
