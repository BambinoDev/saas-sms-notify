# 🔄 Workflow de Versioning - Guide des Instructions

> Guide de référence pour la gestion Git du projet CommCare SMS Automation

**Date de création** : 11 octobre 2025  
**Responsable versioning** : AI Assistant (Cursor)  
**Dépôt** : https://github.com/BambinoDev/saas-sms-notify.git

---

## 🎯 Philosophie du Workflow

**Vous donnez une instruction simple → Je gère tout le processus Git de manière professionnelle**

---

## 📋 Instructions Standardisées

### 1. 💾 Sauvegarder les changements actuels

#### **Instruction simple** :
```
"Sauvegarde les changements"
OU
"Commit les modifications"
OU
"Enregistre mon travail"
```

#### **Ce que je ferai automatiquement** :
1. `git status` → Analyser les fichiers modifiés
2. Vous montrer un résumé des changements
3. Créer un message de commit approprié basé sur :
   - Type de fichiers modifiés (feat/fix/docs/style/refactor)
   - Contexte des changements
4. `git add .` → Ajouter les fichiers
5. `git commit -m "message approprié"` → Commit
6. `git push origin develop` → Pousser vers GitHub

#### **Options supplémentaires** :
```
"Sauvegarde avec le message : [votre message]"
→ J'utiliserai votre message personnalisé

"Sauvegarde sans push"
→ Je commiterai localement sans pousser vers GitHub
```

---

### 2. 🌿 Créer une nouvelle fonctionnalité

#### **Instruction simple** :
```
"Nouvelle feature : [nom]"
OU
"Crée une branche feature/[nom]"
OU
"Je commence à travailler sur [nom]"
```

#### **Ce que je ferai automatiquement** :
1. `git checkout develop` → Revenir à develop
2. `git pull origin develop` → Synchroniser
3. `git checkout -b feature/[nom]` → Créer la branche
4. Confirmer la création
5. Vous indiquer que vous pouvez commencer à travailler

#### **Exemple** :
```
Vous : "Nouvelle feature : notifications-email"
Moi : Je crée feature/notifications-email depuis develop
```

---

### 3. ✅ Terminer une fonctionnalité

#### **Instruction simple** :
```
"Termine la feature"
OU
"Merge la feature dans develop"
OU
"La feature est prête"
```

#### **Ce que je ferai automatiquement** :
1. Vérifier qu'il n'y a pas de modifications non commitées
2. Si oui → Les commiter d'abord
3. `git checkout develop` → Revenir à develop
4. `git pull origin develop` → Synchroniser
5. `git merge feature/[nom]` → Merger la feature
6. `git push origin develop` → Pousser
7. `git branch -d feature/[nom]` → Supprimer la branche locale
8. `git push origin --delete feature/[nom]` → Supprimer sur GitHub
9. Confirmer le merge

---

### 4. 🐛 Corriger un bug

#### **Instruction simple** :
```
"Bug à corriger : [nom]"
OU
"Crée une branche bugfix/[nom]"
```

#### **Ce que je ferai automatiquement** :
1. Même processus qu'une feature
2. Mais avec préfixe `bugfix/` au lieu de `feature/`
3. Message de commit avec type `fix:`

---

### 5. 🚀 Créer une nouvelle version

#### **Instruction simple** :
```
"Nouvelle version : [numéro]"
OU
"Release v[numéro]"
OU
"On passe en v[numéro]"
```

#### **Ce que je ferai automatiquement** :
1. Vérifier que develop est à jour
2. `git checkout main` → Aller sur main
3. `git merge develop` → Merger develop dans main
4. `git tag -a v[numéro] -m "Description"` → Créer le tag
5. `git push origin main --tags` → Pousser tout
6. `git checkout develop` → Revenir sur develop
7. Créer un résumé de la release
8. Vous proposer de créer une GitHub Release

#### **Format des versions** :
- **v1.0.0** → Version majeure (changements importants)
- **v1.1.0** → Version mineure (nouvelles fonctionnalités)
- **v1.1.1** → Patch (corrections de bugs)

#### **Exemple** :
```
Vous : "Nouvelle version : 1.1.0"
Moi : Je merge develop → main, je crée le tag v1.1.0, je pousse tout
```

---

### 6. 🔄 Synchroniser avec GitHub

#### **Instruction simple** :
```
"Synchronise avec GitHub"
OU
"Pull les derniers changements"
OU
"Mets à jour depuis GitHub"
```

#### **Ce que je ferai automatiquement** :
1. Identifier la branche actuelle
2. `git pull origin [branche]` → Récupérer les changements
3. Vous informer des mises à jour

---

### 7. 🔙 Annuler / Revenir en arrière

#### **Instruction simple** :
```
"Annule le dernier commit"
OU
"Reviens au commit [hash]"
OU
"Annule mes modifications"
```

#### **Ce que je ferai automatiquement** :

**Pour annuler le dernier commit (non poussé)** :
```bash
git reset --soft HEAD~1  # Garde les modifications
```

**Pour annuler les modifications non commitées** :
```bash
git restore .  # Annule les changements dans le working directory
```

**Pour revenir à un commit spécifique** :
```bash
git revert [hash]  # Crée un nouveau commit qui annule
```

⚠️ **Je demanderai TOUJOURS confirmation avant d'annuler quoi que ce soit**

---

### 8. 📊 Voir l'état / historique

#### **Instruction simple** :
```
"État du projet"
OU
"Historique des commits"
OU
"Qu'est-ce qui a changé ?"
```

#### **Ce que je ferai automatiquement** :
1. `git status` → État actuel
2. `git log --oneline --graph -10` → Historique
3. `git diff` → Différences non commitées (si demandé)
4. Résumé en français clair

---

### 9. 🔍 Rechercher dans l'historique

#### **Instruction simple** :
```
"Trouve le commit où [description]"
OU
"Quand a été modifié [fichier] ?"
```

#### **Ce que je ferai automatiquement** :
1. `git log --grep="[recherche]"` → Rechercher dans les messages
2. `git log --all -- [fichier]` → Historique d'un fichier
3. Vous présenter les résultats

---

### 10. 🏷️ Gérer les tags

#### **Instruction simple** :
```
"Liste les versions"
OU
"Supprime le tag v[numéro]"
OU
"Crée un tag v[numéro]"
```

#### **Ce que je ferai automatiquement** :
1. `git tag -l` → Lister les tags
2. Créer/supprimer selon demande
3. Synchroniser avec GitHub

---

## 🎨 Messages de Commit Automatiques

Je génère automatiquement des messages de commit selon la convention **Conventional Commits** :

### Types de commits

| Type | Description | Exemple |
|------|-------------|---------|
| `feat` | Nouvelle fonctionnalité | `feat(sms): ajouter support multi-langues` |
| `fix` | Correction de bug | `fix(auth): corriger redirect après login` |
| `docs` | Documentation | `docs(readme): mise à jour installation` |
| `style` | Formatage code | `style: formater code selon PSR-12` |
| `refactor` | Refactoring | `refactor(services): simplifier SmsService` |
| `test` | Tests | `test(sms): ajouter tests unitaires` |
| `chore` | Maintenance | `chore: mise à jour dépendances` |
| `perf` | Performance | `perf(db): optimiser requête femmes` |

### Comment je choisis le type ?

**J'analyse les fichiers modifiés** :
- `*.md`, `docs/` → `docs:`
- `tests/` → `test:`
- Nouveaux fichiers → `feat:`
- Modifications de logique → `feat:` ou `fix:`
- Refactoring sans nouvelle feature → `refactor:`

---

## 📅 Workflow Quotidien Type

### Début de journée

**Vous** : "Synchronise avec GitHub"  
**Moi** : Je pull les derniers changements

### Pendant le développement

**Vous** : "Nouvelle feature : export-excel"  
**Moi** : Je crée la branche feature/export-excel

*[Vous développez...]*

**Vous** : "Sauvegarde les changements"  
**Moi** : Je commit et push avec un message approprié

*[Vous continuez à développer...]*

**Vous** : "Sauvegarde les changements"  
**Moi** : Je commit et push à nouveau

### Fin de feature

**Vous** : "Termine la feature"  
**Moi** : Je merge dans develop et nettoie

### Fin de sprint / Release

**Vous** : "Nouvelle version : 1.2.0"  
**Moi** : Je merge dans main, crée le tag, et pousse tout

---

## 🚨 Situations Spéciales

### Conflit lors du merge

**Vous** : "Il y a un conflit"  
**Moi** : 
1. J'identifie les fichiers en conflit
2. Je vous montre les conflits
3. Je vous demande comment résoudre
4. Une fois résolu, je finalise le merge

### Erreur dans le dernier commit

**Vous** : "J'ai fait une erreur dans le commit"  
**Moi** :
1. Si non poussé → `git commit --amend`
2. Si déjà poussé → Je crée un nouveau commit correctif

### Changer de branche avec modifications en cours

**Vous** : "Change de branche mais sauvegarde mes modifs"  
**Moi** :
1. `git stash` → Sauvegarder temporairement
2. Changer de branche
3. `git stash pop` → Restaurer (si demandé)

---

## 📝 Résumé des Instructions Rapides

| Action | Instruction | Résultat |
|--------|-------------|----------|
| Sauvegarder | "Sauvegarde" | Commit + push |
| Nouvelle feature | "Feature : [nom]" | Crée branche feature/[nom] |
| Terminer feature | "Termine la feature" | Merge dans develop |
| Bug | "Bug : [nom]" | Crée branche bugfix/[nom] |
| Version | "Version : [X.Y.Z]" | Tag + release |
| Synchro | "Synchronise" | Pull depuis GitHub |
| État | "État du projet" | Status + historique |
| Annuler | "Annule" | Reset/revert selon contexte |

---

## 🎯 Principes que je respecte TOUJOURS

### ✅ Je fais automatiquement :
- Analyser les changements
- Créer des messages de commit clairs
- Respecter les conventions de nommage
- Synchroniser avec GitHub
- Gérer les branches proprement
- Nettoyer après les merges

### ⚠️ Je demande TOUJOURS confirmation pour :
- Annuler des commits
- Supprimer des branches avec code non mergé
- Force push
- Modifications sur la branche main
- Suppression de tags

### ❌ Je ne fais JAMAIS :
- `git push --force` sur main/develop
- Commit de fichiers sensibles (.env)
- Modifications destructives sans confirmation
- Merge de code avec conflits non résolus

---

## 🔧 Configuration Automatique

Quand vous me demandez une action Git, je :

1. **Vérifie toujours l'état** avant d'agir
2. **Explique ce que je vais faire** en français clair
3. **Exécute les commandes** de manière professionnelle
4. **Confirme le résultat** et vous informe
5. **Gère les erreurs** et propose des solutions

---

## 📖 Exemples de Conversations

### Exemple 1 : Développement simple

**Vous** : "Sauvegarde mes changements"  
**Moi** :
```
✅ Modifications détectées :
- app/Services/SmsService.php (modifié)
- resources/js/Pages/Dashboard.vue (modifié)

📝 Message de commit : "feat(sms): améliorer service d'envoi SMS"
🚀 Commit créé et poussé vers GitHub
```

### Exemple 2 : Nouvelle fonctionnalité

**Vous** : "Nouvelle feature : export-rapports"  
**Moi** :
```
🌿 Création de la branche feature/export-rapports
✅ Vous êtes maintenant sur feature/export-rapports
💡 Vous pouvez commencer à développer !
```

*[Développement...]*

**Vous** : "Sauvegarde"  
**Moi** :
```
✅ Modifications commitées
📝 "feat(reports): ajouter export Excel des rapports"
🚀 Poussé vers GitHub
```

**Vous** : "Termine la feature"  
**Moi** :
```
🔄 Merge de feature/export-rapports dans develop
✅ Merge réussi
🧹 Branche feature/export-rapports supprimée
🚀 develop poussé vers GitHub
```

### Exemple 3 : Release

**Vous** : "Nouvelle version : 1.2.0"  
**Moi** :
```
📦 Préparation de la release v1.2.0

Changements depuis v1.1.0 :
- feat(reports): export Excel
- feat(sms): multi-langues
- fix(auth): correction redirect
- docs: mise à jour README

🔀 Merge develop → main
🏷️ Tag v1.2.0 créé
🚀 Tout poussé vers GitHub

✅ Release v1.2.0 prête !
💡 Voulez-vous que je crée une GitHub Release ?
```

---

## 🎓 Pour aller plus loin

### Commandes que vous pouvez me demander d'exécuter

Si vous avez besoin d'une commande Git spécifique :

**Vous** : "Exécute : [commande git]"  
**Moi** : J'exécute et vous montre le résultat

### Voir le guide détaillé

Pour plus de détails sur Git et GitHub, consultez `GITHUB_SETUP.md`

---

## 📞 Questions fréquentes

**Q : Puis-je personnaliser le message de commit ?**  
R : Oui ! Dites : "Sauvegarde avec le message : [votre message]"

**Q : Comment voir ce qui a changé avant de commit ?**  
R : Dites : "Qu'est-ce qui a changé ?" ou "Montre les différences"

**Q : Je veux travailler sur plusieurs features en même temps ?**  
R : Pas de problème ! Je gère les changements de branches avec `git stash`

**Q : Comment revenir à une version précédente ?**  
R : Dites : "Reviens à la version [numéro]" ou "Reviens au commit [hash]"

**Q : Tu peux faire des PR GitHub ?**  
R : Pour l'instant, je gère Git en local. Les PR se font sur l'interface GitHub.

---

## ✅ Checklist de démarrage

Pour que je puisse gérer efficacement le versioning :

- ✅ Git initialisé
- ✅ Remote GitHub configuré
- ✅ Branches main et develop créées
- ✅ Vous êtes sur la branche develop
- ✅ Vous connaissez les instructions de base
- ✅ Ce guide est dans le projet

**🎉 Vous êtes prêt ! Donnez-moi simplement vos instructions.**

---

**Version du document** : 1.0  
**Dernière mise à jour** : 11 octobre 2025  
**Maintenu par** : AI Assistant

---

## 🎯 Résumé Ultra-Court

**Vous me dites simplement** :
- "Sauvegarde" → Je commit et push
- "Feature : nom" → Je crée la branche
- "Termine la feature" → Je merge dans develop
- "Version : X.Y.Z" → Je crée la release
- "État" → Je vous montre où on en est

**C'est aussi simple que ça ! 🚀**

