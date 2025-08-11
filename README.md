# MyPet 🐾

Une application web complète pour la gestion de vos animaux de compagnie, construite avec **Nuxt.js 3** (frontend) et **Symfony 6** (backend).

## ✨ Fonctionnalités

- 🐕 **Gestion des animaux** : Ajout, modification et suivi de vos compagnons
- 📅 **Calendrier** : Planification des rendez-vous vétérinaires et soins
- ✅ **Checklists** : Suivi des tâches quotidiennes et soins
- 📝 **Blog** : Partage d'expériences et conseils
- 🔐 **Authentification** : Système de connexion sécurisé
- 👑 **Administration** : Interface d'administration complète

## 🛠️ Technologies utilisées

### Frontend
- **Nuxt.js 3** - Framework Vue.js full-stack
- **Vue 3** - Framework JavaScript progressif
- **Tailwind CSS** - Framework CSS utilitaire
- **Pinia** - Gestionnaire d'état

### Backend
- **Symfony 6** - Framework PHP moderne
- **Doctrine ORM** - Mapping objet-relationnel
- **API Platform** - API REST/GraphQL
- **JWT** - Authentification sécurisée
- **MySQL/PostgreSQL** - Base de données

## 🚀 Installation

### Prérequis
- Node.js 18+ 
- PHP 8.1+
- Composer
- MySQL ou PostgreSQL

### Frontend (Nuxt.js)
```bash
# Installer les dépendances
npm install

# Démarrer en mode développement
npm run dev

# Construire pour la production
npm run build
```

### Backend (Symfony)
```bash
cd backend

# Installer les dépendances
composer install

# Configurer la base de données
cp .env .env.local
# Modifier .env.local avec vos paramètres de base de données

# Créer la base de données
php bin/console doctrine:database:create

# Exécuter les migrations
php bin/console doctrine:migrations:migrate

# Démarrer le serveur
symfony server:start
```


# Modifier les variables selon votre configuration
# Base de données, clés API, etc.
```

### 6. Démarrer l'application
```bash
# Terminal 1 - Backend Symfony
cd backend
symfony server:start

# Terminal 2 - Frontend Nuxt
npm run dev
```

L'application sera accessible sur :
- **Frontend** : http://localhost:3000
- **Backend** : http://localhost:8000

### Frontend
```bash
npm run build
# Les fichiers générés sont dans le dossier .output/
```

### Backend
```bash
# Développement
npm run dev          # Démarrer le serveur de développement
npm run build        # Construire l'application pour la production
npm run preview      # Prévisualiser la version de production

# Backend
php bin/console cache:clear    # Vider le cache
php bin/console doctrine:schema:update --force  # Mettre à jour la base
```

## 📄 Licence

Ce projet est sous licence MIT. Voir le fichier `LICENSE` pour plus de détails.

**Développé avec ❤️ en Valais par Julia Varone**
