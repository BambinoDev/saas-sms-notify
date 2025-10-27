# 📋 RAPPORT DE PRÉPARATION - TESTS ONBOARDING

**Date**: 13 octobre 2025 - 00:33 UTC  
**Status**: ✅ **PRÊT POUR TESTS**  
**Type**: Tests manuels du flux complet d'onboarding

---

## ✅ ÉTAT DU SYSTÈME

### Infrastructure Docker
| Container | Status | Ports |
|-----------|--------|-------|
| notify_sms_app | ✅ Up 23 hours | 9000 |
| notify_sms_nginx | ✅ Up 2 days | 8080→80 |
| notify_sms_node | ✅ Up 2 days | 5174→5173 |
| notify_sms_postgres | ✅ Up 2 days | 5433→5432 |
| notify_sms_redis | ✅ Up 2 days | 6380→6379 |
| notify_sms_worker | ✅ Up 2 days | 9000 |
| notify_sms_scheduler | ✅ Up 2 days | 9000 |
| notify_sms_mailhog | ✅ Up 2 days | 8026→8025 |
| notify_sms_pgadmin | ✅ Up 2 days | 5051→80 |

**Résultat**: ✅ Tous les containers sont opérationnels

### Frontend Build
- **Status**: ✅ Présent
- **Date**: 12 octobre 2025 - 23:31
- **Taille**: 12 KB (manifest.json)
- **Path**: `public/build/manifest.json`

### Composants Vue.js Onboarding
- ✅ `Pages/Onboarding/Welcome.vue`
- ✅ `Pages/Onboarding/Company.vue`
- ✅ `Pages/Onboarding/CommCare.vue`
- ✅ `Pages/Onboarding/Mapping.vue`
- ✅ `Pages/Onboarding/Completion.vue`
- ✅ `Pages/Onboarding/Phone.vue`

### Routes Laravel
Toutes les routes d'onboarding sont configurées :
```
GET  /onboarding/welcome        → OnboardingController@welcome
GET  /onboarding/company        → OnboardingController@company
POST /onboarding/company        → OnboardingController@storeCompany
GET  /onboarding/commcare       → OnboardingController@commcare
POST /onboarding/commcare/test  → OnboardingController@testCommcare
POST /onboarding/commcare       → OnboardingController@storeCommcare
GET  /onboarding/mapping        → OnboardingController@mapping
POST /onboarding/mapping/properties → OnboardingController@fetchCaseProperties
POST /onboarding/mapping        → OnboardingController@storeMapping
GET  /onboarding/completion     → OnboardingController@completion
```

---

## 👥 UTILISATEURS ET ORGANISATIONS

### Utilisateur de test 1 (RECOMMANDÉ)
**Credentials**:
- **Email**: `admin@notify-sms.local`
- **Nom**: Admin Central
- **Organisation**: Ministère de la Santé - Côte d'Ivoire
- **Organisation ID**: 1
- **Organisation Slug**: `ministere-sante-ci`

**État onboarding**:
- ✅ **Step**: 1/5 (Welcome)
- ✅ **Completed**: NO
- ✅ **Réinitialisé**: Oui (13/10/2025)

**Données actuelles**:
```json
{
  "organization_type": null,
  "industry": null,
  "timezone": "Africa/Abidjan",
  "team_size": null,
  "project_description": null,
  "commcare_email": null,
  "commcare_project_space": null,
  "commcare_case_type": null,
  "phone_number_field": null,
  "onboarding_step": 1,
  "onboarding_completed": false
}
```

### Utilisateur de test 2 (Alternative)
**Credentials**:
- **Email**: `dupontjean@mailo.com`
- **Nom**: Dupont Jean
- **Organisation**: CH
- **Organisation ID**: 5
- **Organisation Slug**: `ch`

**État onboarding**:
- **Step**: 3/5 (CommCare) - En cours
- **Completed**: NO

---

## 🔑 INFORMATIONS DE CONNEXION

### Application Web
- **URL**: http://localhost:8080
- **Connexion**: http://localhost:8080/login

### Base de données PostgreSQL
- **Host**: localhost
- **Port**: 5433
- **Database**: notify_sms
- **Username**: notify_sms_user
- **Password**: [voir .env]

### pgAdmin
- **URL**: http://localhost:5051
- **Email**: admin@notify-sms.local
- **Password**: admin

### MailHog (Test emails)
- **URL**: http://localhost:8026
- **SMTP**: localhost:1026

### Redis
- **Host**: localhost
- **Port**: 6380

---

## 🧪 CREDENTIALS COMMCARE POUR TESTS

### ✅ Credentials valides (À utiliser pour test succès)
```
Email:          lucas.sidibe@savethechildren.org
Project Space:  sci-civ-malaria
API Key:        f976f7136f793ff1989aed85529fd673ecddb696
```

**Expected**: Connexion réussie ✅

### ❌ Credentials invalides (À utiliser pour test échec)
```
Email:          wrong@email.com
Project Space:  wrong-project
API Key:        wrong_api_key_123456
```

**Expected**: Erreur HTTP 401 ❌

---

## 🛠️ OUTILS DE TEST DISPONIBLES

### Script Helper: `test-onboarding.sh`

**Localisation**: `/Users/rodsid/SCI/SCI/DevProject/SAAS CommCare SMS/test-onboarding.sh`

**Commandes disponibles**:

```bash
# Réinitialiser l'onboarding (recommencer from scratch)
./test-onboarding.sh reset

# Afficher l'état actuel
./test-onboarding.sh status

# Vérifier les données après étape 2 (Company)
./test-onboarding.sh check-step2

# Vérifier les données après étape 3 (CommCare)
./test-onboarding.sh check-step3

# Vérifier les données après étape 4 (Mapping)
./test-onboarding.sh check-step4

# Vérification finale complète
./test-onboarding.sh check-final

# Forcer un step spécifique (debugging)
./test-onboarding.sh set-step 3

# Vérifier l'existence de l'organisation
./test-onboarding.sh check-org

# Logs en temps réel
./test-onboarding.sh logs
```

### Guide de tests
**Localisation**: `/Users/rodsid/SCI/SCI/DevProject/SAAS CommCare SMS/GUIDE_TESTS_ONBOARDING.md`

Ce guide contient :
- ✅ Checklist visuelle pour chaque étape
- ✅ Tests de validation (cas d'erreur)
- ✅ Tests de sauvegarde (cas de succès)
- ✅ Vérifications de base de données
- ✅ Solutions aux erreurs fréquentes

---

## 📝 SCÉNARIOS DE TEST

### Scénario 1 : Flux complet happy path (RECOMMANDÉ)

**Objectif**: Tester le flux complet sans erreur

**Steps**:
1. ✅ Connexion avec `admin@notify-sms.local`
2. ✅ Étape 1 (Welcome) → Cliquer "Commencer"
3. ✅ Étape 2 (Company) → Remplir formulaire valide → Continuer
4. ✅ Étape 3 (CommCare) → Tester avec credentials valides → Continuer
5. ✅ Étape 4 (Mapping) → Charger properties "woman" → Sélectionner → Définir phone field → Terminer
6. ✅ Étape 5 (Completion) → Vérifier récap → Accéder au dashboard

**Temps estimé**: 10-15 minutes

**Vérifications après chaque étape**:
```bash
./test-onboarding.sh check-step2  # Après étape 2
./test-onboarding.sh check-step3  # Après étape 3
./test-onboarding.sh check-step4  # Après étape 4
./test-onboarding.sh check-final  # Après étape 5
```

### Scénario 2 : Tests de validation

**Objectif**: Vérifier que les validations fonctionnent

**Tests**:
1. ❌ Étape 2 : Soumettre formulaire vide → Vérifier erreurs
2. ❌ Étape 2 : Taper plus de 500 caractères dans description → Vérifier limite
3. ❌ Étape 3 : Tester avec credentials invalides → Vérifier erreur HTTP 401
4. ❌ Étape 4 : Charger case_type invalide → Vérifier erreur
5. ❌ Étape 4 : Tenter de soumettre sans phone field → Vérifier alert
6. ❌ Étape 4 : Tenter de soumettre sans propriété → Vérifier alert

**Temps estimé**: 5-10 minutes

### Scénario 3 : Tests de navigation

**Objectif**: Vérifier les boutons "Retour"

**Tests**:
1. Étape 2 → Cliquer "Retour" → Vérifier redirection vers Étape 1
2. Étape 3 → Cliquer "Retour" → Vérifier redirection vers Étape 2
3. Étape 4 → Cliquer "Retour" → Vérifier redirection vers Étape 3

**Temps estimé**: 3-5 minutes

---

## 🎯 CHECKLIST PRÉ-TESTS

Avant de commencer les tests, vérifier :

- [x] ✅ Docker containers actifs (9/9)
- [x] ✅ Frontend build présent
- [x] ✅ Organisation réinitialisée (step 1)
- [x] ✅ Script helper créé et exécutable
- [x] ✅ Guide de tests disponible
- [ ] ⬜ Navigateur ouvert sur http://localhost:8080
- [ ] ⬜ Terminal ouvert pour commandes helper
- [ ] ⬜ DevTools (F12) ouverts pour vérifier console
- [ ] ⬜ Credentials CommCare à portée de main

---

## 📊 MÉTRIQUES ATTENDUES

### Performance
- ⏱️ Temps de réponse API : < 3 secondes
- ⏱️ Temps total onboarding : 5-10 minutes
- ⏱️ Chargement properties CommCare : < 3 secondes

### UX
- ✅ Progress bar visible et mise à jour à chaque étape
- ✅ Loading states visibles pendant les requêtes AJAX
- ✅ Messages success/error clairs et explicites
- ✅ Pas d'erreur JavaScript dans la console

### Base de données
- ✅ API Key encryptée en base
- ✅ Toutes les données sauvegardées correctement
- ✅ `onboarding_completed` = true à la fin
- ✅ `onboarding_completed_at` renseigné

---

## 🚀 LANCEMENT DES TESTS

### Étape 1 : Vérifier l'état actuel
```bash
cd "/Users/rodsid/SCI/SCI/DevProject/SAAS CommCare SMS"
./test-onboarding.sh status
```

**Output attendu**:
```
📋 Organisation: Ministère de la Santé - Côte d'Ivoire
📊 Onboarding Step: 1/5
✅ Completed: NO
```

### Étape 2 : Ouvrir l'application
```bash
open http://localhost:8080
```

### Étape 3 : Se connecter
- Email: `admin@notify-sms.local`
- Password: [mot de passe admin]

### Étape 4 : Naviguer vers onboarding
```
http://localhost:8080/onboarding/welcome
```

### Étape 5 : Suivre le guide de tests
Ouvrir le fichier `GUIDE_TESTS_ONBOARDING.md` et suivre les instructions pas à pas.

---

## 📞 SUPPORT

### Logs Laravel
```bash
tail -f storage/logs/laravel.log
# OU
./test-onboarding.sh logs
```

### Logs Docker
```bash
docker logs -f notify_sms_app
docker logs -f notify_sms_node
```

### Clearing cache
```bash
docker exec notify_sms_app php artisan cache:clear
docker exec notify_sms_app php artisan config:clear
docker exec notify_sms_app php artisan route:clear
docker exec notify_sms_app php artisan view:clear
```

### Rebuild frontend
```bash
docker exec notify_sms_node npm run build
```

---

## ✅ VALIDATION FINALE

Après avoir complété tous les tests, exécuter :

```bash
./test-onboarding.sh check-final
```

**Output attendu** (si succès) :
```
========================================
   🎉 ONBOARDING 100% VALIDÉ ✅ 🎉      
========================================
```

---

## 📝 NOTES IMPORTANTES

1. **Organisation testée**: Ministère de la Santé - Côte d'Ivoire (ID: 1)
2. **Utilisateur test**: admin@notify-sms.local
3. **Case type à utiliser**: `woman`
4. **Propriétés recommandées**: case_id, case_name, phone_number, next_visit_date, lmp, edd
5. **Phone field**: `phone_number` ou `contact_phone_number`

---

## 🎉 PRÊT POUR LES TESTS

**Tout est en place** pour commencer les tests de l'onboarding !

👉 **Prochaine étape** : Ouvrir `GUIDE_TESTS_ONBOARDING.md` et commencer les tests.

**Bonne chance ! 🚀**

