# 🧪 GUIDE DE TESTS ONBOARDING - CommCare SMS SAAS

**Date**: 13 octobre 2025  
**Status**: ✅ Prêt pour tests  
**Organisation**: Ministère de la Santé - Côte d'Ivoire  
**Onboarding Step**: 1/5 (Reset effectué)

---

## 📋 PRÉ-REQUIS

✅ **État du système vérifié**:
- ✅ Docker containers actifs (9/9)
- ✅ Frontend build présent (12 oct 23:31)
- ✅ Organisation réinitialisée (step 1)
- ✅ Base de données opérationnelle
- ✅ Redis cache actif
- ✅ Worker queues actifs

**URL de test**: http://localhost:8080

---

## 🚀 DÉROULEMENT DES TESTS

### Étape 0 : Connexion utilisateur

1. Ouvrir http://localhost:8080
2. Se connecter avec les credentials de test
3. Vérifier redirection vers dashboard ou onboarding

---

## 🧪 TEST ÉTAPE 1 : WELCOME

### URL de test
```
http://localhost:8080/onboarding/welcome
```

### ✅ Checklist visuelle

- [ ] Header gradient bleu affiché
- [ ] Icône fusée 🚀 visible
- [ ] Titre : "Bienvenue sur S-Remind"
- [ ] Nom organisation affiché : "Ministère de la Santé - Côte d'Ivoire"
- [ ] 5 étapes listées (Welcome, Company, CommCare, Mapping, Completion)
- [ ] Étape 1 avec icône verte ✓
- [ ] 3 métriques affichées (~5 min, 100%, ∞)
- [ ] Bouton "Commencer la configuration" visible et cliquable

### ✅ Test fonctionnel

**Action**: Cliquer sur "Commencer la configuration"  
**Résultat attendu**: Redirection vers `/onboarding/company`

### ✅ Vérification console

Ouvrir DevTools (F12) → Console
Vérifier qu'il n'y a **aucune erreur JavaScript**

**Statut**: ⬜ À tester

---

## 🧪 TEST ÉTAPE 2 : COMPANY

### URL de test
```
http://localhost:8080/onboarding/company
```

### ✅ Checklist visuelle

- [ ] Progress bar affiche étape 2/5 active (bleu)
- [ ] Progress bar affiche étape 1 complétée (✓)
- [ ] Titre : "Informations sur votre organisation"
- [ ] 5 champs affichés :
  - [ ] Type d'organisation (select)
  - [ ] Secteur d'activité (select)
  - [ ] Fuseau horaire (select)
  - [ ] Taille de l'équipe (select)
  - [ ] Description du projet (textarea)
- [ ] Compteur caractères (0 / 500) visible sur textarea
- [ ] Bouton "Retour" visible
- [ ] Bouton "Continuer" visible

### ✅ Test validation (cas d'erreur)

**Test 1 : Formulaire vide**
1. Laisser tous les champs vides
2. Cliquer sur "Continuer"
3. **Résultat attendu**: Messages d'erreur rouges sous champs requis

**Test 2 : Description trop longue**
1. Taper plus de 500 caractères dans Description
2. **Résultat attendu**: Impossible de taper au-delà de 500

### ✅ Test sauvegarde (cas de succès)

**Remplir le formulaire**:
- Type d'organisation : `Hôpital`
- Secteur d'activité : `Santé`
- Fuseau horaire : `Africa/Abidjan`
- Taille de l'équipe : `10-50 personnes`
- Description : `Test du système d'onboarding SAAS`

**Action**: Cliquer sur "Continuer"

**Résultat attendu**:
- Loading state ("Sauvegarde...")
- Redirection vers `/onboarding/commcare`

### ✅ Vérification base de données

```bash
# Utiliser le helper script
./test-onboarding.sh check-step2
```

**Statut**: ⬜ À tester

---

## 🧪 TEST ÉTAPE 3 : COMMCARE

### URL de test
```
http://localhost:8080/onboarding/commcare
```

### ✅ Checklist visuelle

- [ ] Progress bar affiche étape 3/5 active
- [ ] Étapes 1-2 cochées (✓)
- [ ] Section "Tester la connexion" visible
- [ ] 3 champs :
  - [ ] Email CommCare
  - [ ] Project Space
  - [ ] API Key (type password, masqué)
- [ ] Bouton "Tester la connexion" désactivé si champs vides
- [ ] Section configuration masquée (grise)

### ✅ Test 1 : Mauvais credentials

**Remplir**:
- Email : `wrong@email.com`
- Project Space : `wrong-project`
- API Key : `wrong_api_key_123456`

**Action**: Cliquer sur "Tester la connexion"

**Résultats attendus**:
- Bouton devient "Test en cours..." avec spinner
- Après ~2-3 secondes : Message d'erreur rouge
- Icône ❌ rouge visible
- Message : "Échec de connexion (HTTP 401)..."
- Section configuration reste masquée

### ✅ Test 2 : Bons credentials

**Remplir**:
- Email : `lucas.sidibe@savethechildren.org`
- Project Space : `sci-civ-malaria`
- API Key : `f976f7136f793ff1989aed85529fd673ecddb696`

**Action**: Cliquer sur "Tester la connexion"

**Résultats attendus**:
- Bouton devient "Test en cours..." avec spinner
- Après ~2-3 secondes : Message de succès vert
- Icône ✅ verte visible
- Message : "Connexion réussie ! X application(s) trouvée(s)"
- Section configuration apparaît avec :
  - [ ] Champ App ID (pré-rempli ou vide)
  - [ ] Champ Project Name (pré-rempli ou vide)
  - [ ] Warning ambre "Sécurité"
  - [ ] Bouton "Continuer" actif

### ✅ Test 3 : Sauvegarde configuration

**Compléter**:
- App ID : (laisser pré-rempli ou taper manuellement)
- Project Name : `Malaria Prevention Côte d'Ivoire`

**Action**: Cliquer sur "Continuer"

**Résultat attendu**:
- Loading state ("Sauvegarde...")
- Redirection vers `/onboarding/mapping`

### ✅ Vérification base de données

```bash
# Utiliser le helper script
./test-onboarding.sh check-step3
```

### ✅ Test 4 : Vérification logs

```bash
tail -50 storage/logs/laravel.log | grep -E "(CommCare|Onboarding)"
```

**Attendu**:
- Testing CommCare connection
- CommCare connection test successful
- Onboarding CommCare completed

**Statut**: ⬜ À tester

---

## 🧪 TEST ÉTAPE 4 : MAPPING

### URL de test
```
http://localhost:8080/onboarding/mapping
```

### ✅ Checklist visuelle

- [ ] Progress bar affiche étape 4/5 active
- [ ] Étapes 1-3 cochées (✓)
- [ ] Section 1 : "Type de case CommCare" visible
- [ ] Input case_type + bouton "Charger les propriétés"
- [ ] Section 2 et 3 masquées (placeholder gris)

### ✅ Test 1 : Case type invalide

**Action**:
1. Taper : `invalid_case_type_xyz`
2. Cliquer sur "Charger les propriétés"

**Résultat attendu**:
- Loading ("Chargement...")
- Après 2-3s : Message d'erreur rouge
- Message : "Aucun case trouvé pour le type 'invalid_case_type_xyz'..."

### ✅ Test 2 : Case type valide (woman)

**Action**:
1. Taper : `woman`
2. Cliquer sur "Charger les propriétés"

**Résultats attendus**:
- Loading ("Chargement...")
- Après 2-3s : Section 2 apparaît "Sélection des propriétés"
- Info box bleu : "63 propriétés trouvées" (ou similaire)
- Grid de propriétés affiché (2 colonnes sur desktop)
- Chaque propriété a :
  - [ ] Checkbox
  - [ ] Nom en police mono
  - [ ] Bouton "Définir comme champ téléphone" (si sélectionné)
- Bouton "Tout sélectionner" visible en haut à droite
- Section 3 apparaît : "Condition d'éligibilité (Optionnel)"

### ✅ Test 3 : Sélection propriétés

**Actions**:

1. **Cliquer sur "Tout sélectionner"**  
   **Résultat** : Toutes les 63 propriétés cochées, fond bleu

2. **Cliquer à nouveau sur "Tout désélectionner"**  
   **Résultat** : Tout décoché, fond blanc

3. **Sélectionner manuellement ces propriétés**:
   - ✓ case_id
   - ✓ case_name
   - ✓ phone_number (ou contact_phone_number)
   - ✓ next_visit_date
   - ✓ lmp (dernières règles)
   - ✓ edd (date accouchement)

**Résultat attendu**:
- Propriétés sélectionnées ont fond bleu
- Bouton "Définir comme champ téléphone" apparaît
- Summary en bas : "6 propriété(s) sélectionnée(s)"

### ✅ Test 4 : Définir champ téléphone

**Action**: Cliquer sur "Définir comme champ téléphone" sur `phone_number`

**Résultat attendu**:
- Bouton devient vert avec "✓ Champ téléphone"
- Summary affiche : "✓ Champ téléphone : phone_number"

### ✅ Test 5 : Condition d'éligibilité (optionnel)

**Actions**:
1. Dans "Champ à vérifier" : Sélectionner `consent_sms_yes`
2. Dans "Condition" : Laisser "Est égal à"
3. Dans "Valeur attendue" : Taper `yes`

**Résultat attendu**:
- Box violet apparaît avec exemple :  
  "Seuls les cases où consent_sms_yes est égal à yes recevront des SMS"

### ✅ Test 6 : Validation formulaire

**Test 6a : Sans champ téléphone**
1. Désélectionner le champ téléphone (cliquer dessus à nouveau)
2. Cliquer sur "Terminer la configuration"
3. **Résultat** : Alert "Veuillez sélectionner... et définir le champ téléphone"

**Test 6b : Sans propriété sélectionnée**
1. Tout désélectionner
2. Cliquer sur "Terminer la configuration"
3. **Résultat** : Alert "Veuillez sélectionner au moins une propriété..."

### ✅ Test 7 : Sauvegarde réussie

**Action**:
1. Sélectionner au moins 3 propriétés
2. Définir `phone_number` comme champ téléphone
3. (Optionnel) Configurer condition éligibilité
4. Cliquer sur "Terminer la configuration"

**Résultat attendu**:
- Loading ("Sauvegarde...")
- Redirection vers `/onboarding/completion`

### ✅ Vérification base de données

```bash
# Utiliser le helper script
./test-onboarding.sh check-step4
```

**Statut**: ⬜ À tester

---

## 🧪 TEST ÉTAPE 5 : COMPLETION

### URL de test
```
http://localhost:8080/onboarding/completion
```

### ✅ Checklist visuelle

- [ ] Header gradient vert/bleu affiché
- [ ] Grande icône ✅ blanche sur fond vert
- [ ] Titre : "Félicitations ! 🎉"
- [ ] Sous-titre : "Votre configuration est terminée"
- [ ] Nom organisation affiché en gras
- [ ] 4 cartes récapitulatives :
  - [ ] 🏢 Organisation (nom + type)
  - [ ] 🔗 CommCare (projet + domain)
  - [ ] 📋 Case Type (type + nombre propriétés)
  - [ ] 📱 Champ téléphone (nom du champ)
- [ ] Section "Prochaines étapes" avec 3 étapes numérotées
- [ ] Bouton "Accéder au Dashboard" avec icône fusée
- [ ] Liens support en bas (documentation, support)

### ✅ Test fonctionnel

**Action**: Cliquer sur "Accéder au Dashboard"

**Résultat attendu**:
- Redirection vers `/dashboard`
- Dashboard principal s'affiche

### ✅ Vérification base de données (FINALE)

```bash
# Utiliser le helper script
./test-onboarding.sh check-final
```

**Statut**: ⬜ À tester

---

## 📊 CHECKLIST GLOBALE DE VALIDATION

### ✅ Tests fonctionnels
- [ ] Étape 1 : Welcome → Redirection Company
- [ ] Étape 2 : Company → Validation + Sauvegarde + Redirection CommCare
- [ ] Étape 3 : CommCare → Test AJAX (échec + succès) + Sauvegarde + Redirection Mapping
- [ ] Étape 4 : Mapping → Load properties AJAX + Sélection + Validation + Sauvegarde + Redirection Completion
- [ ] Étape 5 : Completion → Affichage récap + Redirection Dashboard

### ✅ Tests validation
- [ ] Formulaire Company vide → Erreurs
- [ ] Test CommCare mauvais credentials → Erreur rouge
- [ ] Test CommCare bons credentials → Succès vert
- [ ] Mapping case_type invalide → Erreur
- [ ] Mapping sans champ téléphone → Alert
- [ ] Mapping sans propriété → Alert

### ✅ Tests base de données
- [ ] Company data sauvegardée (step 3)
- [ ] CommCare data + API Key encrypted (step 4)
- [ ] Mapping data + JSON properties (step 5)
- [ ] onboarding_completed = true
- [ ] onboarding_completed_at renseigné

### ✅ Tests UX
- [ ] Progress bar mise à jour à chaque étape
- [ ] Loading states visibles
- [ ] Messages success/error clairs
- [ ] Boutons "Retour" fonctionnels
- [ ] Responsive (tester sur mobile si possible)

### ✅ Tests sécurité
- [ ] Routes protégées par auth (logout puis essayer /onboarding/company → redirect login)
- [ ] API Key masquée en frontend (type password)
- [ ] API Key encryptée en DB
- [ ] Validation stricte des inputs

---

## 🐛 ERREURS FRÉQUENTES ET SOLUTIONS

### Erreur 1 : "Organization not found"

**Solution**:
```bash
./test-onboarding.sh check-org
```

### Erreur 2 : "Route not defined"

**Solution**:
```bash
docker exec notify_sms_app php artisan route:clear
docker exec notify_sms_app php artisan config:clear
```

### Erreur 3 : AJAX call failed

**Solution**:
```bash
# Vérifier les logs
tail -f storage/logs/laravel.log
# Vérifier DevTools → Network → Voir la requête AJAX
```

### Erreur 4 : Page blanche

**Solution**:
```bash
# Rebuild frontend
docker exec notify_sms_node npm run build
# Vider cache navigateur (Ctrl + Shift + R)
```

---

## 🔧 COMMANDES UTILES

### Reset onboarding (recommencer from scratch)
```bash
./test-onboarding.sh reset
```

### Vérifier l'état actuel
```bash
./test-onboarding.sh status
```

### Passer à une étape spécifique (pour debugging)
```bash
./test-onboarding.sh set-step 3  # Force step 3
```

### Voir les logs en temps réel
```bash
./test-onboarding.sh logs
```

---

## 📝 TEMPLATE DE RAPPORT DE TEST

Après avoir terminé tous les tests, remplir ce template :

```markdown
# RAPPORT DE TEST ONBOARDING - [DATE]

## ✅ Tests réussis
- [x] Étape 1 : Welcome
- [x] Étape 2 : Company
- [x] Étape 3 : CommCare
- [x] Étape 4 : Mapping
- [x] Étape 5 : Completion

## ❌ Bugs trouvés
1. [Description du bug]
   - Étape : [Numéro]
   - Gravité : [Critique / Majeur / Mineur]
   - Reproduction : [Steps]

## 📊 Statistiques
- Temps total de test : XX minutes
- Nombre d'erreurs : X
- Taux de réussite : XX%

## 💡 Améliorations suggérées
1. [Suggestion 1]
2. [Suggestion 2]

## ✅ Validation finale
- [ ] Onboarding complet testé sans erreur
- [ ] Base de données correctement mise à jour
- [ ] UX fluide et intuitive
- [ ] Prêt pour production
```

---

## 🚀 COMMENCER LES TESTS

1. **Ouvrir le navigateur** : http://localhost:8080
2. **Se connecter** avec les credentials
3. **Suivre ce guide** étape par étape
4. **Cocher les cases** ✅ au fur et à mesure
5. **Noter les bugs** dans le rapport
6. **Valider la configuration finale** en base de données

**Bonne chance ! 🎉**

