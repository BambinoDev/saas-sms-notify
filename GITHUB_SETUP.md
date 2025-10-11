# 🚀 Configuration GitHub - CommCare SMS Automation

> Instructions complètes pour mettre le projet sur GitHub

**Date de création** : 11 octobre 2025  
**Statut Git local** : ✅ Initialisé et prêt

---

## ✅ État actuel

- ✅ Dépôt Git initialisé
- ✅ Commit initial créé (23aff28)
- ✅ Branche `main` créée (production stable)
- ✅ Branche `develop` créée (développement actif)
- ✅ Tag `v1.0.0` créé
- ✅ `.gitignore` configuré et optimisé
- ✅ README.md professionnel créé

**223 fichiers** indexés et commités ✅

---

## 📋 Étape 1 : Créer le dépôt GitHub

### Option A : Via l'interface web GitHub

1. **Aller sur GitHub** : https://github.com/new

2. **Remplir les informations** :
   - **Repository name** : `commcare-sms-automation`
   - **Description** : `Plateforme SaaS Multi-tenant de rappels SMS automatiques pour consultations prénatales via CommCare`
   - **Visibility** : 
     - ✅ `Private` (recommandé pour code propriétaire)
     - ⚠️ `Public` (si open source)
   - ⚠️ **NE PAS** cocher "Initialize with README"
   - ⚠️ **NE PAS** ajouter `.gitignore`
   - ⚠️ **NE PAS** choisir une licence maintenant

3. **Cliquer sur** : "Create repository"

### Option B : Via GitHub CLI (si installé)

```bash
# Installer GitHub CLI si nécessaire
brew install gh

# Se connecter à GitHub
gh auth login

# Créer le dépôt
gh repo create commcare-sms-automation \
  --private \
  --description "Plateforme SaaS Multi-tenant de rappels SMS automatiques pour consultations prénatales via CommCare" \
  --source=. \
  --push
```

---

## 📋 Étape 2 : Connecter le dépôt local à GitHub

### 2.1 Ajouter le remote GitHub

Après avoir créé le dépôt sur GitHub, vous recevrez une URL. Utilisez-la :

```bash
cd "/Users/rodsid/SCI/SCI/DevProject/SAAS CommCare SMS"

# Ajouter le remote (remplacer YOUR_USERNAME par votre nom d'utilisateur GitHub)
git remote add origin https://github.com/YOUR_USERNAME/commcare-sms-automation.git

# OU avec SSH (si configuré)
git remote add origin git@github.com:YOUR_USERNAME/commcare-sms-automation.git

# Vérifier le remote
git remote -v
```

### 2.2 Pousser le code vers GitHub

```bash
# Pousser la branche main
git push -u origin main

# Pousser la branche develop
git push -u origin develop

# Pousser tous les tags
git push --tags
```

---

## 📋 Étape 3 : Configurer les branches sur GitHub

### 3.1 Protéger la branche `main`

1. Aller dans **Settings** > **Branches**
2. Cliquer sur **Add rule**
3. **Branch name pattern** : `main`
4. Activer :
   - ✅ **Require a pull request before merging**
   - ✅ **Require approvals** (1 minimum)
   - ✅ **Dismiss stale pull request approvals when new commits are pushed**
   - ✅ **Require status checks to pass before merging**
   - ✅ **Require branches to be up to date before merging**
   - ✅ **Include administrators**
5. **Save changes**

### 3.2 Définir `develop` comme branche par défaut

1. Aller dans **Settings** > **Branches**
2. **Default branch** > Switch to `develop`
3. Confirmer

---

## 📋 Étape 4 : Ajouter des collaborateurs (optionnel)

1. Aller dans **Settings** > **Collaborators and teams**
2. Cliquer sur **Add people**
3. Entrer le nom d'utilisateur GitHub
4. Choisir le rôle :
   - `Admin` : Accès complet
   - `Maintain` : Gérer sans accès aux paramètres sensibles
   - `Write` : Push et merge
   - `Read` : Lecture seule

---

## 📋 Étape 5 : Configuration des secrets (pour CI/CD futur)

Si vous prévoyez d'utiliser GitHub Actions, configurez les secrets :

1. Aller dans **Settings** > **Secrets and variables** > **Actions**
2. Ajouter les secrets nécessaires :
   - `COMMCARE_API_KEY`
   - `AFRICAS_TALKING_API_KEY`
   - `AFRICAS_TALKING_USERNAME`
   - etc.

---

## 🌿 Workflow Git recommandé

### Structure des branches

```
main (production)
  └─ develop (développement)
      ├─ feature/nouvelle-fonctionnalite
      ├─ bugfix/correction-bug
      └─ hotfix/correction-urgente (peut partir de main)
```

### Créer une nouvelle fonctionnalité

```bash
# Partir de develop
git checkout develop
git pull origin develop

# Créer une branche feature
git checkout -b feature/nom-de-la-fonctionnalite

# Développer et commiter
git add .
git commit -m "feat: description de la fonctionnalité"

# Pousser vers GitHub
git push -u origin feature/nom-de-la-fonctionnalite

# Créer une Pull Request sur GitHub vers develop
```

### Merge vers production

```bash
# Quand develop est stable
git checkout main
git merge develop
git tag -a v1.1.0 -m "Description de la version"
git push origin main --tags
```

---

## 📝 Convention de commits

Suivre la convention **Conventional Commits** :

```
type(scope): description courte

[corps du message optionnel]

[footer optionnel]
```

### Types de commits

- `feat`: Nouvelle fonctionnalité
- `fix`: Correction de bug
- `docs`: Documentation uniquement
- `style`: Formatage, points-virgules, etc. (pas de changement de code)
- `refactor`: Refactoring de code
- `test`: Ajout de tests
- `chore`: Tâches de maintenance, config, etc.
- `perf`: Amélioration de performance
- `ci`: Modifications CI/CD

### Exemples

```bash
feat(sms): ajouter support pour plusieurs langues
fix(auth): corriger la redirection après login
docs(readme): mettre à jour les instructions d'installation
refactor(services): simplifier CommCareService
test(sms): ajouter tests unitaires pour SmsService
```

---

## 🔄 Commandes Git utiles

### Vérifier l'état

```bash
# État du working directory
git status

# Historique des commits
git log --oneline --graph --all

# Différences
git diff
```

### Synchronisation

```bash
# Récupérer les changements
git fetch origin

# Récupérer et merger
git pull origin develop

# Pousser vers GitHub
git push origin develop
```

### Gestion des branches

```bash
# Lister les branches
git branch -a

# Créer une nouvelle branche
git checkout -b feature/nouvelle-feature

# Changer de branche
git checkout develop

# Supprimer une branche locale
git branch -d feature/ancienne-feature

# Supprimer une branche distante
git push origin --delete feature/ancienne-feature
```

### Tags

```bash
# Créer un tag annoté
git tag -a v1.1.0 -m "Version 1.1.0"

# Lister les tags
git tag -l

# Pousser les tags
git push --tags

# Supprimer un tag local
git tag -d v1.0.0

# Supprimer un tag distant
git push origin --delete v1.0.0
```

---

## 🔐 Sécurité

### Vérifications avant de pousser

✅ **Vérifier qu'aucune donnée sensible n'est présente** :

```bash
# Vérifier les fichiers à commiter
git status

# Vérifier le contenu
git diff --cached

# Chercher des secrets potentiels
grep -r "password\|secret\|api_key" --include="*.php" --include="*.env"
```

⚠️ **Fichiers à ne JAMAIS commiter** :

- `.env` (déjà dans `.gitignore`)
- Clés API, tokens, mots de passe
- Fichiers de configuration avec données sensibles
- Bases de données SQLite avec données réelles

### Si vous avez commité des secrets par erreur

```bash
# NE PAS utiliser git reset si déjà poussé !
# Utiliser plutôt :
git filter-branch --force --index-filter \
  "git rm --cached --ignore-unmatch chemin/vers/fichier" \
  --prune-empty --tag-name-filter cat -- --all

# Ou utiliser BFG Repo-Cleaner (plus rapide)
# https://rtyley.github.io/bfg-repo-cleaner/
```

---

## 📊 État final après setup

Après avoir suivi ces instructions, vous aurez :

```
GitHub Repository
├── Branch: main (default: develop)
│   ├── Protected
│   └── Tag: v1.0.0
├── Branch: develop
│   └── Active development
└── Collaborators (si ajoutés)
```

**Votre dépôt local** :

```
Local Repository
├── Remote: origin -> https://github.com/YOUR_USERNAME/commcare-sms-automation.git
├── Branch: main (tracking origin/main)
├── Branch: develop (tracking origin/develop) ← Current
└── Tag: v1.0.0
```

---

## 🆘 Aide & Troubleshooting

### Erreur : "remote origin already exists"

```bash
# Supprimer le remote existant
git remote remove origin

# Ajouter le nouveau
git remote add origin https://github.com/YOUR_USERNAME/commcare-sms-automation.git
```

### Erreur : "fatal: refusing to merge unrelated histories"

```bash
# Forcer la fusion (première fois seulement)
git pull origin main --allow-unrelated-histories
```

### Erreur : "Authentication failed"

```bash
# Utiliser un Personal Access Token au lieu du mot de passe
# Créer un token : https://github.com/settings/tokens

# Ou configurer SSH
ssh-keygen -t ed25519 -C "your_email@example.com"
cat ~/.ssh/id_ed25519.pub # Ajouter à GitHub
```

### Voir les différences entre local et remote

```bash
# Fetch sans merger
git fetch origin

# Comparer
git diff develop origin/develop
```

---

## 📞 Support

Pour toute question sur Git ou GitHub :

- **Documentation Git** : https://git-scm.com/doc
- **Documentation GitHub** : https://docs.github.com/
- **GitHub Skills** : https://skills.github.com/

---

## ✅ Checklist finale

Avant de dire "C'est terminé" :

- [ ] Dépôt GitHub créé
- [ ] Remote origin configuré
- [ ] Branches `main` et `develop` poussées
- [ ] Tag `v1.0.0` poussé
- [ ] Branche `develop` définie comme défaut
- [ ] Branche `main` protégée
- [ ] README.md visible sur GitHub
- [ ] Collaborateurs ajoutés (si nécessaire)
- [ ] `.env` vérifié (non commité)
- [ ] Secrets GitHub configurés (si CI/CD)

---

**Dernière mise à jour** : 11 octobre 2025  
**Par** : AI Assistant  
**Version du document** : 1.0

🎉 **Félicitations ! Votre projet est maintenant sous contrôle de version avec Git et GitHub !**

