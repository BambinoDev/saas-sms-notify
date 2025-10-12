# 📱 CommCare SMS Automation Platform

> Plateforme SaaS Multi-tenant de rappels SMS automatiques pour consultations prénatales via CommCare

[![Laravel](https://img.shields.io/badge/Laravel-12.x-FF2D20?style=flat&logo=laravel)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.2+-777BB4?style=flat&logo=php)](https://www.php.net/)
[![Vue.js](https://img.shields.io/badge/Vue.js-3.5-4FC08D?style=flat&logo=vue.js)](https://vuejs.org/)
[![PostgreSQL](https://img.shields.io/badge/PostgreSQL-17.6-316192?style=flat&logo=postgresql)](https://www.postgresql.org/)
[![License](https://img.shields.io/badge/License-Proprietary-red.svg)](LICENSE)

---

## 🎯 À propos

**CommCare SMS Automation** est une plateforme SaaS multi-tenant conçue pour automatiser l'envoi de rappels SMS. La solution synchronise les données depuis CommCare et gère intelligemment l'envoi de SMS via Africa's Talking.

### 🌟 Fonctionnalités principales

- ✅ **Multi-Tenancy** : Architecture multi-tenant avec isolation complète des données
- 🔄 **Synchronisation CommCare** : Import automatique des cas et données femmes
- 📅 **Génération intelligente de SMS** : Rappels J-2 et Jour-J configurables
- 📊 **Dashboard complet** : Suivi en temps réel des envois et statistiques
- 🎨 **Interface moderne** : Vue.js 3 + Inertia.js 2 + TailwindCSS
- 🔐 **Sécurité avancée** : Authentification, autorisation, et isolation des tenants
- 📱 **Validation téléphone** : Gestion intelligente des numéros internationaux
- ⚙️ **Configuration flexible** : Paramétrage par tenant des règles SMS

---

## 🏗️ Architecture technique

### Stack Backend
- **Framework** : Laravel 12.x
- **Langage** : PHP 8.2+
- **Base de données** : PostgreSQL 17.6
- **Cache** : Redis 7.4
- **Queue** : Laravel Database Queue
- **Multi-tenancy** : `stancl/tenancy` v4.x

### Stack Frontend
- **Runtime** : Node.js 22 LTS
- **Framework** : Vue.js 3.5 (Composition API)
- **Bridge** : Inertia.js 2.x
- **Styling** : TailwindCSS 3.4
- **Build** : Vite 5.x

### Infrastructure
- **Containerisation** : Docker + Docker Compose
- **Web Server** : Nginx 1.27-alpine
- **Outils** : pgAdmin 4, MailHog (dev)

---

## 📋 Prérequis

- Docker Desktop 4.x+
- Docker Compose 2.x+
- Git 2.x+
- Compte Africa's Talking (API SMS)
- Compte CommCare avec accès API

---

## 🚀 Installation

### 1. Cloner le projet

```bash
git clone https://github.com/votre-organisation/commcare-sms-automation.git
cd commcare-sms-automation
```

### 2. Configuration de l'environnement

```bash
# Copier le fichier d'environnement
cp env.example .env

# Modifier les variables d'environnement nécessaires
nano .env
```

**Variables importantes à configurer :**

```env
# CommCare API
COMMCARE_API_KEY=votre_api_key
COMMCARE_PROJECT_SPACE=votre_project_space

# Africa's Talking
AFRICAS_TALKING_USERNAME=votre_username
AFRICAS_TALKING_API_KEY=votre_api_key
AFRICAS_TALKING_ENVIRONMENT=production
```

### 3. Démarrer l'environnement Docker

```bash
# Démarrer les conteneurs
docker-compose up -d

# Vérifier que tous les services sont en cours d'exécution
docker-compose ps
```

### 4. Installation des dépendances

```bash
# Backend (PHP)
docker-compose exec app composer install

# Frontend (Node.js)
docker-compose exec app npm install
```

### 5. Configuration de l'application

```bash
# Générer la clé d'application
docker-compose exec app php artisan key:generate

# Exécuter les migrations
docker-compose exec app php artisan migrate

# Seeder les données de base
docker-compose exec app php artisan db:seed

# Créer le lien de storage
docker-compose exec app php artisan storage:link
```

### 6. Build du frontend

```bash
# Mode développement (avec hot reload)
docker-compose exec app npm run dev

# OU Mode production
docker-compose exec app npm run build
```

### 7. Accéder à l'application

- **Application** : http://localhost:8080
- **pgAdmin** : http://localhost:5050
- **MailHog** : http://localhost:8025

**Identifiants par défaut (Super Admin) :**
- Email : `admin@notify-sms.local`
- Mot de passe : `password`

---

## 🏃‍♂️ Utilisation

### Créer un nouveau tenant (Organisation)

```bash
docker-compose exec app php artisan tenants:create organization_name
```

### Lancer la synchronisation CommCare

```bash
docker-compose exec app php artisan commcare:sync {tenant_id}
```

### Générer les SMS

```bash
docker-compose exec app php artisan sms:generate {tenant_id}
```

### Envoyer les SMS en attente

```bash
docker-compose exec app php artisan sms:send-pending
```

### Scheduler (Automatisation)

Le scheduler Laravel s'exécute automatiquement via Docker. Horaires configurés :

- **02:30** : Synchronisation CommCare
- **05:00** : Génération des SMS
- **10:00** : Envoi des SMS J-2
- **06:00** : Envoi des SMS Jour-J

---

## 📚 Documentation

- [Architecture détaillée](docs/ARCHITECTURE.md)
- [API Documentation](docs/API.md)
- [Guide Multi-tenant](FAQ_MULTI_TENANT.md)
- [Sprints & Développement](SPRINT1_INDEX.md)

---

## 🧪 Tests

```bash
# Exécuter tous les tests
docker-compose exec app php artisan test

# Tests avec couverture
docker-compose exec app php artisan test --coverage

# Tests spécifiques
docker-compose exec app php artisan test --filter=SmsGenerationTest
```

---

## 📦 Structure du projet

```
.
├── app/
│   ├── Console/Commands/      # Commandes Artisan
│   ├── Http/Controllers/      # Contrôleurs
│   ├── Jobs/                  # Jobs de queue
│   ├── Models/                # Modèles Eloquent
│   ├── Services/              # Couche métier
│   └── Utils/                 # Utilitaires
├── config/                    # Configuration
├── database/
│   ├── migrations/            # Migrations de base de données
│   └── seeders/               # Seeders
├── docker/                    # Configuration Docker
├── docs/                      # Documentation
├── resources/
│   ├── js/                    # Vue.js components
│   └── views/                 # Blade templates
├── routes/                    # Définition des routes
└── tests/                     # Tests PHPUnit
```

---

## 🔧 Maintenance

### Logs

```bash
# Voir les logs Laravel
docker-compose exec app tail -f storage/logs/laravel.log

# Logs d'un tenant spécifique
docker-compose exec app tail -f storage/logs/tenant-{id}.log
```

### Nettoyage

```bash
# Nettoyer les anciens SMS (archivés)
docker-compose exec app php artisan sms:cleanup-old

# Nettoyer le cache
docker-compose exec app php artisan cache:clear
docker-compose exec app php artisan config:clear
docker-compose exec app php artisan view:clear
```

### Base de données

```bash
# Backup
docker-compose exec postgres pg_dump -U laravel notify_sms_db > backup.sql

# Restore
docker-compose exec -T postgres psql -U laravel notify_sms_db < backup.sql
```

---

## 🤝 Contribution

Ce projet suit les standards de développement définis dans le fichier `.cursorrules`.

### Workflow Git

1. Créer une branche depuis `develop` : `git checkout -b feature/ma-fonctionnalite`
2. Développer et commiter : `git commit -m "feat: description"`
3. Pousser la branche : `git push origin feature/ma-fonctionnalite`
4. Créer une Pull Request vers `develop`

### Standards de code

- **PHP** : PSR-12, Type hints obligatoires
- **Vue.js** : Composition API avec `<script setup>`
- **Commits** : Convention Conventional Commits

---

## 📝 Changelog

Voir [HISTORIQUE_VERSIONS.md](HISTORIQUE_VERSIONS.md) pour l'historique détaillé des versions.

---

## 📄 License

Proprietary - Tous droits réservés © 2025

---

## 👥 Équipe

- **Tech Lead** : [Votre Nom]
- **Organisation** : SCI - Solutions Communautaires Intégrées
- **Contact** : support@votre-domaine.com

---

## 🆘 Support

Pour toute question ou problème :

1. Consulter la [documentation](docs/)
2. Vérifier les [issues existantes](https://github.com/votre-organisation/commcare-sms-automation/issues)
3. Créer une [nouvelle issue](https://github.com/votre-organisation/commcare-sms-automation/issues/new)

---

**Dernière mise à jour** : Octobre 2025  
**Version** : 1.0.0 (Sprint 1 - Multi-tenant)
