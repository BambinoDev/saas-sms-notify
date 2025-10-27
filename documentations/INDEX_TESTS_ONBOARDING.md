# 📚 INDEX - TESTS ONBOARDING

**Date de création**: 13 octobre 2025  
**Version**: 1.0  
**Status**: ✅ Complet et prêt pour utilisation

---

## 🎯 OBJECTIF

Ce dossier contient **tout le nécessaire** pour effectuer des tests complets et professionnels du processus d'onboarding du SAAS CommCare SMS.

**Qu'est-ce qui a été préparé ?**
- ✅ Organisation réinitialisée (step 1/5)
- ✅ Documentation complète
- ✅ Script helper automatisé
- ✅ Template de rapport
- ✅ Guide pas à pas
- ✅ Credentials de test
- ✅ Système opérationnel (Docker, DB, Frontend)

---

## 📂 STRUCTURE DES FICHIERS

### 1. Point d'entrée ⭐
```
COMMENCEZ_ICI_TESTS_ONBOARDING.md
```
**Description**: Fichier principal à lire en premier  
**Contenu**: Quick start, checklist, plan d'action  
**Temps de lecture**: 3-5 minutes  
**👉 COMMENCEZ PAR CELUI-CI !**

---

### 2. État technique complet
```
RAPPORT_PREPARATION_TESTS_ONBOARDING.md
```
**Description**: État détaillé du système  
**Contenu**: 
- Infrastructure Docker (9 containers)
- Composants Vue.js (6 fichiers)
- Routes Laravel (12 routes)
- Utilisateurs et organisations (2 users, 2 orgs)
- Credentials CommCare
- Outils disponibles
- Scénarios de test détaillés

**Utilisation**: Référence technique pendant les tests  
**Temps de lecture**: 10-15 minutes

---

### 3. Guide de tests détaillé
```
GUIDE_TESTS_ONBOARDING.md
```
**Description**: Instructions pas à pas pour chaque étape  
**Contenu**:
- **Étape 1 : Welcome** (checklist visuelle + tests fonctionnels)
- **Étape 2 : Company** (tests validation + sauvegarde)
- **Étape 3 : CommCare** (test AJAX + credentials valides/invalides)
- **Étape 4 : Mapping** (load properties + sélection + validation)
- **Étape 5 : Completion** (récapitulatif + redirection)
- Checklist globale de validation
- Solutions aux erreurs fréquentes

**Utilisation**: À suivre pendant les tests (cocher les cases ✅)  
**Temps d'exécution**: 20-30 minutes (tous les scénarios)

---

### 4. Template de rapport
```
RAPPORT_TESTS_ONBOARDING_TEMPLATE.md
```
**Description**: Rapport de tests vierge à remplir  
**Contenu**:
- Résumé exécutif
- Checklist détaillée par étape
- Section bugs (avec gravité)
- Améliorations suggérées
- Tests additionnels (navigation, sécurité, responsive)
- Validation finale
- Statistiques

**Utilisation**: À remplir pendant/après les tests  
**Format**: Markdown avec checkboxes et zones de texte

---

### 5. Script helper automatisé
```
test-onboarding.sh
```
**Description**: Script Bash pour automatiser les vérifications  
**Commandes disponibles**:
```bash
./test-onboarding.sh reset         # Réinitialiser (step 1)
./test-onboarding.sh status        # État actuel
./test-onboarding.sh check-step2   # Vérifier étape 2
./test-onboarding.sh check-step3   # Vérifier étape 3
./test-onboarding.sh check-step4   # Vérifier étape 4
./test-onboarding.sh check-final   # Vérification finale
./test-onboarding.sh set-step N    # Forcer un step (debug)
./test-onboarding.sh check-org     # Vérifier organisation
./test-onboarding.sh logs          # Logs temps réel
```

**Utilisation**: Exécuter après chaque étape pour valider en DB  
**Permissions**: Exécutable (chmod +x déjà fait)

---

### 6. Ce fichier (Index)
```
INDEX_TESTS_ONBOARDING.md
```
**Description**: Table des matières et guide d'utilisation  
**Contenu**: Vue d'ensemble de tous les fichiers  
**Vous êtes ici ! 📍**

---

## 🚀 WORKFLOW RECOMMANDÉ

### Phase 1 : Préparation (5 min)

1. ✅ Lire `COMMENCEZ_ICI_TESTS_ONBOARDING.md`
2. ✅ Vérifier les pré-requis :
   ```bash
   ./test-onboarding.sh status
   ```
3. ✅ Ouvrir 3 fenêtres :
   - Navigateur : http://localhost:8080
   - `GUIDE_TESTS_ONBOARDING.md`
   - Terminal pour commandes helper

---

### Phase 2 : Tests fonctionnels (15-20 min)

4. ✅ **Scénario 1 : Happy Path**
   - Suivre `GUIDE_TESTS_ONBOARDING.md`
   - Étape 1 → 2 → 3 → 4 → 5
   - Vérifier DB après chaque étape

5. ✅ **Scénario 2 : Validations**
   - Tester les cas d'erreur
   - Formulaires vides
   - Credentials invalides

6. ✅ **Scénario 3 : Navigation**
   - Tester boutons "Retour"
   - Vérifier redirections

---

### Phase 3 : Reporting (5-10 min)

7. ✅ Remplir `RAPPORT_TESTS_ONBOARDING_TEMPLATE.md`
   - Cocher les tests effectués
   - Noter les bugs trouvés
   - Ajouter suggestions d'amélioration

8. ✅ Validation finale :
   ```bash
   ./test-onboarding.sh check-final
   ```

9. ✅ Décider : ☐ Validé pour production  ☐ Non validé

---

## 📊 CE QUI EST TESTÉ

### Fonctionnalités (40 tests)
| Catégorie | Nombre de tests |
|-----------|----------------|
| Affichage visuel | 12 tests |
| Navigation | 4 tests |
| Validation formulaires | 6 tests |
| Requêtes AJAX | 4 tests |
| Sauvegarde DB | 5 tests |
| Sécurité | 4 tests |
| UX/Loading states | 5 tests |

**Total** : ~40 points de vérification

---

## 🎯 DONNÉES DE TEST

### Utilisateur recommandé
```
Email:    admin@notify-sms.local
Password: [votre mot de passe admin]
Org:      Ministère de la Santé - Côte d'Ivoire (ID: 1)
Step:     1/5 (Welcome)
```

### Credentials CommCare valides
```
Email:          lucas.sidibe@savethechildren.org
Project Space:  sci-civ-malaria
API Key:        f976f7136f793ff1989aed85529fd673ecddb696
```

### Credentials CommCare invalides (pour test erreur)
```
Email:          wrong@email.com
Project Space:  wrong-project
API Key:        wrong_api_key_123456
```

### Données Mapping
```
Case Type:     woman
Propriétés:    case_id, case_name, phone_number, next_visit_date, lmp, edd
Phone Field:   phone_number
```

---

## ✅ ÉTAT ACTUEL DU SYSTÈME

### Infrastructure
- ✅ Docker : 9 containers actifs
- ✅ Application : http://localhost:8080
- ✅ Database : PostgreSQL 17 (port 5433)
- ✅ Redis : Port 6380
- ✅ MailHog : http://localhost:8026
- ✅ pgAdmin : http://localhost:5051

### Frontend
- ✅ Build : Présent (12 oct 23:31)
- ✅ Composants Vue : 6 fichiers onboarding
- ✅ Routes : 12 routes configurées

### Backend
- ✅ Laravel : 12.x
- ✅ PHP : 8.2+
- ✅ Controllers : OnboardingController opérationnel
- ✅ Models : Organization prêt

### Base de données
- ✅ Organisation : Ministère de la Santé (ID: 1)
- ✅ Step : 1/5 (Welcome)
- ✅ Completed : NO
- ✅ Données : Réinitialisées

---

## 🐛 SUPPORT

### Si problème pendant les tests

1. **Consulter les logs** :
   ```bash
   ./test-onboarding.sh logs
   ```

2. **Vérifier l'organisation** :
   ```bash
   ./test-onboarding.sh check-org
   ```

3. **Recommencer from scratch** :
   ```bash
   ./test-onboarding.sh reset
   ```

4. **Clearing cache** :
   ```bash
   docker exec notify_sms_app php artisan cache:clear
   docker exec notify_sms_app php artisan config:clear
   ```

5. **Rebuild frontend** :
   ```bash
   docker exec notify_sms_node npm run build
   ```

### Erreurs fréquentes

| Erreur | Solution |
|--------|----------|
| "Organization not found" | `./test-onboarding.sh check-org` |
| "Route not defined" | `php artisan route:clear` |
| AJAX call failed | Vérifier logs + DevTools Network |
| Page blanche | Rebuild frontend + vider cache navigateur |

---

## 📞 RÉFÉRENCES

### Documentation projet
- `HANDOVER_NOUVEAU_LEAD_OCT2025.md` - Documentation complète
- `ARCHITECTURE_CLIENT_VS_SUPERADMIN.md` - Architecture multi-tenant
- `SPRINT1_INDEX.md` - Historique Sprint 1

### URLs utiles
- Application : http://localhost:8080
- Onboarding : http://localhost:8080/onboarding/welcome
- Dashboard : http://localhost:8080/dashboard
- MailHog : http://localhost:8026
- pgAdmin : http://localhost:5051

---

## 🎉 RÉSUMÉ

**Vous avez à disposition** :
- ✅ 6 fichiers de documentation
- ✅ 1 script automatisé
- ✅ 3 scénarios de test
- ✅ ~40 points de vérification
- ✅ Système opérationnel
- ✅ Données de test prêtes

**Temps estimé total** : 30-40 minutes (préparation + tests + rapport)

**Prêt à commencer ?** 👉 Ouvrez `COMMENCEZ_ICI_TESTS_ONBOARDING.md`

---

## 📝 CHECKLIST FINALE

Avant de commencer, vérifiez que vous avez :

- [ ] ✅ Lu ce fichier (INDEX)
- [ ] ✅ Ouvert `COMMENCEZ_ICI_TESTS_ONBOARDING.md`
- [ ] ✅ Vérifié le système : `./test-onboarding.sh status`
- [ ] ✅ Navigateur ouvert sur http://localhost:8080
- [ ] ✅ Terminal prêt pour commandes helper
- [ ] ✅ DevTools (F12) ouverts
- [ ] ✅ `GUIDE_TESTS_ONBOARDING.md` sous les yeux
- [ ] ✅ `RAPPORT_TESTS_ONBOARDING_TEMPLATE.md` prêt à remplir
- [ ] ✅ Credentials CommCare à portée de main

**Tout est coché ?** 🎉 **Vous êtes prêt !**

---

**Créé le** : 13 octobre 2025  
**Version** : 1.0  
**Status** : ✅ Production Ready  
**Bonne chance avec vos tests ! 🚀**

