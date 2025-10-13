# 🚀 COMMENCEZ ICI - TESTS ONBOARDING

**Date de préparation**: 13 octobre 2025  
**Status**: ✅ **PRÊT POUR TESTS**

---

## 📋 QUE CONTIENT CE DOSSIER ?

### Documents créés pour vous

| Document | Description | Utilisation |
|----------|-------------|-------------|
| **`COMMENCEZ_ICI_TESTS_ONBOARDING.md`** (ce fichier) | Point d'entrée principal | Lisez-moi en premier ! |
| **`RAPPORT_PREPARATION_TESTS_ONBOARDING.md`** | État complet du système | Référence technique |
| **`GUIDE_TESTS_ONBOARDING.md`** | Guide détaillé étape par étape | Suivez ce guide pendant les tests |
| **`RAPPORT_TESTS_ONBOARDING_TEMPLATE.md`** | Template de rapport vierge | Remplissez pendant/après les tests |
| **`test-onboarding.sh`** | Script helper automatisé | Commandes de vérification DB |

---

## ⚡ DÉMARRAGE RAPIDE (5 MINUTES)

### Étape 1 : Vérifier que tout est prêt

```bash
cd "/Users/rodsid/SCI/SCI/DevProject/SAAS CommCare SMS"

# Vérifier les containers Docker
docker ps | grep notify_sms

# Vérifier l'état de l'onboarding
./test-onboarding.sh status
```

**Output attendu**:
```
📋 Organisation: Ministère de la Santé - Côte d'Ivoire
📊 Onboarding Step: 1/5
✅ Completed: NO
```

✅ **Si vous voyez ça, c'est bon !**

---

### Étape 2 : Ouvrir l'application dans le navigateur

```bash
open http://localhost:8080
```

**OU** : Ouvrir manuellement votre navigateur sur `http://localhost:8080`

---

### Étape 3 : Se connecter

**Credentials recommandés** :
- **Email** : `admin@notify-sms.local`
- **Password** : [votre mot de passe admin]

**Organisation testée** : Ministère de la Santé - Côte d'Ivoire

---

### Étape 4 : Accéder à l'onboarding

Taper directement dans la barre d'adresse :
```
http://localhost:8080/onboarding/welcome
```

Vous devriez voir la **page Welcome** avec :
- 🚀 Une icône fusée
- Le titre "Bienvenue sur S-Remind"
- Un bouton "Commencer la configuration"

✅ **Vous êtes prêt à tester !**

---

## 📖 COMMENT EFFECTUER LES TESTS ?

### Méthode recommandée

1. **Ouvrir 3 fenêtres côte à côte** :
   
   **Fenêtre 1** : Navigateur avec l'application  
   **Fenêtre 2** : `GUIDE_TESTS_ONBOARDING.md` (suivre instructions)  
   **Fenêtre 3** : Terminal (pour commandes helper)

2. **Suivre le guide étape par étape**
   - Lire la checklist visuelle
   - Effectuer les tests
   - Cocher les cases ✅
   - Noter les bugs

3. **Vérifier la base de données après chaque étape**
   ```bash
   ./test-onboarding.sh check-step2  # Après étape 2
   ./test-onboarding.sh check-step3  # Après étape 3
   ./test-onboarding.sh check-step4  # Après étape 4
   ./test-onboarding.sh check-final  # Après étape 5
   ```

4. **Remplir le rapport de tests**
   - Ouvrir `RAPPORT_TESTS_ONBOARDING_TEMPLATE.md`
   - Remplir au fur et à mesure
   - Noter les bugs et suggestions

---

## 🧪 SCÉNARIOS DE TEST

### Scénario 1 : Happy Path (RECOMMANDÉ POUR COMMENCER)

**Durée estimée** : 10-15 minutes

**Objectif** : Tester le flux complet sans erreur

**Steps** :
1. ✅ Welcome → Cliquer "Commencer"
2. ✅ Company → Remplir formulaire → Continuer
3. ✅ CommCare → Tester connexion valide → Continuer
4. ✅ Mapping → Charger properties "woman" → Sélectionner → Continuer
5. ✅ Completion → Vérifier récap → Accéder dashboard

**Credentials CommCare à utiliser** :
```
Email:          lucas.sidibe@savethechildren.org
Project Space:  sci-civ-malaria
API Key:        f976f7136f793ff1989aed85529fd673ecddb696
Case Type:      woman
```

---

### Scénario 2 : Tests de Validation

**Durée estimée** : 5-10 minutes

**Objectif** : Vérifier que les validations fonctionnent

**Tests** :
- ❌ Soumettre formulaire vide → Vérifier erreurs
- ❌ Tester avec credentials invalides → Vérifier erreur HTTP 401
- ❌ Charger case_type invalide → Vérifier erreur
- ❌ Soumettre sans phone field → Vérifier alert

---

### Scénario 3 : Tests de Navigation

**Durée estimée** : 3-5 minutes

**Objectif** : Vérifier les boutons "Retour"

**Tests** :
- Étape 2 → Retour → Vérifier redirection Étape 1
- Étape 3 → Retour → Vérifier redirection Étape 2
- Étape 4 → Retour → Vérifier redirection Étape 3

---

## 🛠️ COMMANDES HELPER UTILES

### Pendant les tests

```bash
# Voir l'état actuel
./test-onboarding.sh status

# Vérifier après étape 2
./test-onboarding.sh check-step2

# Vérifier après étape 3
./test-onboarding.sh check-step3

# Vérifier après étape 4
./test-onboarding.sh check-step4

# Vérification finale
./test-onboarding.sh check-final

# Voir les logs en temps réel
./test-onboarding.sh logs
```

### Si problème

```bash
# Recommencer from scratch
./test-onboarding.sh reset

# Vérifier l'organisation
./test-onboarding.sh check-org

# Clearing cache Laravel
docker exec notify_sms_app php artisan cache:clear
docker exec notify_sms_app php artisan config:clear

# Rebuild frontend (si page blanche)
docker exec notify_sms_node npm run build
```

---

## 🎯 CHECKLIST PRÉ-TESTS

Avant de commencer, cochez :

- [ ] ✅ Docker containers actifs (vérifier avec `docker ps`)
- [ ] ✅ Application accessible sur http://localhost:8080
- [ ] ✅ Organisation réinitialisée (step 1)
- [ ] ✅ Script helper exécutable
- [ ] ✅ Navigateur ouvert
- [ ] ✅ Terminal ouvert
- [ ] ✅ DevTools (F12) ouverts pour console
- [ ] ✅ Credentials CommCare à portée de main
- [ ] ✅ `GUIDE_TESTS_ONBOARDING.md` ouvert
- [ ] ✅ `RAPPORT_TESTS_ONBOARDING_TEMPLATE.md` ouvert

---

## 📊 CE QUI SERA TESTÉ

### Fonctionnalités
- ✅ Affichage de chaque étape de l'onboarding
- ✅ Navigation entre les étapes (Next/Back)
- ✅ Progress bar mise à jour
- ✅ Formulaires de saisie
- ✅ Validations côté client
- ✅ Validations côté serveur
- ✅ Requêtes AJAX (test CommCare, load properties)
- ✅ Sauvegarde en base de données
- ✅ Encryption API Key
- ✅ Redirection finale vers dashboard

### UX
- ✅ Loading states visibles
- ✅ Messages success/error clairs
- ✅ Interface responsive
- ✅ Pas d'erreur console

### Sécurité
- ✅ Routes protégées (auth required)
- ✅ API Key masquée (type password)
- ✅ API Key encryptée en DB
- ✅ Validation stricte des inputs

---

## 🐛 SI VOUS TROUVEZ UN BUG

### Étapes à suivre

1. **Noter les détails** :
   - Quelle étape ?
   - Quelle action ?
   - Quel résultat attendu ?
   - Quel résultat obtenu ?

2. **Prendre un screenshot** si possible

3. **Copier les logs** :
   ```bash
   tail -50 storage/logs/laravel.log
   ```

4. **Remplir dans le rapport** :
   - Ouvrir `RAPPORT_TESTS_ONBOARDING_TEMPLATE.md`
   - Section "Bugs trouvés"
   - Détailler le bug

5. **Gravité** :
   - **Critique** : Bloque complètement l'onboarding
   - **Majeur** : Fonctionnalité majeure ne marche pas
   - **Mineur** : UX/UI, problème cosmétique

---

## ✅ VALIDATION FINALE

### Après avoir complété tous les tests

1. **Exécuter la vérification finale** :
   ```bash
   ./test-onboarding.sh check-final
   ```

2. **Résultat attendu** :
   ```
   ========================================
      🎉 ONBOARDING 100% VALIDÉ ✅ 🎉      
   ========================================
   ```

3. **Vérifier le dashboard** :
   - Accéder à http://localhost:8080/dashboard
   - Vérifier que les données s'affichent

4. **Compléter le rapport** :
   - Remplir la section "Validation finale"
   - Cocher "✅ VALIDÉ POUR PRODUCTION" ou autre

---

## 📞 SUPPORT & RÉFÉRENCES

### Documentation disponible

| Fichier | Contenu |
|---------|---------|
| `HANDOVER_NOUVEAU_LEAD_OCT2025.md` | Documentation complète du projet |
| `RAPPORT_PREPARATION_TESTS_ONBOARDING.md` | État technique complet |
| `GUIDE_TESTS_ONBOARDING.md` | Instructions détaillées pas à pas |

### Commandes utiles

```bash
# Logs Laravel
tail -f storage/logs/laravel.log

# Logs Docker
docker logs -f notify_sms_app

# Routes Laravel
docker exec notify_sms_app php artisan route:list --path=onboarding

# DB Tinker
docker exec -it notify_sms_app php artisan tinker
```

---

## 🎉 PRÊT À COMMENCER ?

### Plan d'action suggéré

1. **Lire ce document** (vous y êtes !) ✅
2. **Vérifier les pré-requis** (section checklist)
3. **Ouvrir le guide de tests** (`GUIDE_TESTS_ONBOARDING.md`)
4. **Ouvrir le template de rapport** (`RAPPORT_TESTS_ONBOARDING_TEMPLATE.md`)
5. **Lancer le scénario 1** (Happy Path)
6. **Vérifier avec les commandes helper**
7. **Remplir le rapport**
8. **Lancer les autres scénarios**
9. **Validation finale**

---

## 📝 NOTES IMPORTANTES

- **Temps estimé total** : 20-30 minutes pour tous les tests
- **Organisation testée** : Ministère de la Santé - Côte d'Ivoire (ID: 1)
- **User test** : admin@notify-sms.local
- **Case type recommandé** : `woman`
- **Propriétés recommandées** : case_id, case_name, phone_number, next_visit_date, lmp, edd
- **Phone field** : `phone_number` ou `contact_phone_number`

---

## 🚀 COMMENCEZ MAINTENANT !

**Tout est prêt !** Vous avez tous les outils nécessaires pour effectuer des tests complets et professionnels de l'onboarding.

👉 **Prochaine étape** : Ouvrir `GUIDE_TESTS_ONBOARDING.md` et commencer le Scénario 1.

**Bonne chance ! 🎉**

---

**Questions ?** Consultez le fichier `RAPPORT_PREPARATION_TESTS_ONBOARDING.md` pour plus de détails techniques.

