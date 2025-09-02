# Movie Collection App

Une application Symfony pour gérer et afficher une collection de films avec des fonctionnalités de navigation et de recherche.

## Fonctionnalités

- **Page d'accueil** : Affichage des films en grille avec pagination (12 films par page)
- **Détail des films** : Page complète avec informations sur le film, acteurs, réalisateur, studio et tags
- **Gestion des studios** : Liste et pages détail des studios avec leurs films
- **Gestion des réalisateurs** : Liste et filmographies des réalisateurs
- **Gestion des acteurs** : Liste et filmographies des acteurs  
- **Recherche** : Recherche globale dans les titres, acteurs, réalisateurs et studios
- **Import de données** : Commandes console pour importer des données depuis des fichiers CSV
- **Popup d'avertissement** : Avertissement contenu adulte configurable avec sélecteur de langue
- **Support multilingue** : Interface disponible en français, anglais et espagnol

## Structure du projet

```
src/
├── Command/           # Commandes d'import
├── Controller/        # Contrôleurs de l'application
├── Entity/           # Entités Doctrine (Movie, Studio, Director, Actor, Tag)
├── Repository/       # Repositories Doctrine
├── Service/         # Services métier
└── DataFixtures/    # Fixtures pour les données de test

templates/
├── base.html.twig   # Template de base
├── home/           # Templates de la page d'accueil
├── movie/          # Templates des films
├── studio/         # Templates des studios
├── director/       # Templates des réalisateurs
├── actor/          # Templates des acteurs
└── search/         # Templates de recherche

assets/
├── styles/         # Fichiers CSS
└── scripts/        # Fichiers JavaScript

data/               # Fichiers CSV d'exemple
```

## Installation

### Prérequis

- PHP 8.1 ou supérieur
- Composer
- MySQL/MariaDB
- Serveur web (Apache/Nginx) ou serveur de développement Symfony

### Étapes d'installation

1. **Installer les dépendances** :
   ```bash
   composer install
   ```

2. **Configurer la base de données** :
   - Modifier le fichier `.env` avec vos paramètres de base de données :
     ```
     DATABASE_URL="mysql://username:password@127.0.0.1:3306/movie_app?serverVersion=8.0&charset=utf8mb4"
     ```

3. **Créer la base de données** :
   ```bash
   php bin/console doctrine:database:create
   ```

4. **Exécuter les migrations** :
   ```bash
   php bin/console doctrine:migrations:migrate
   ```

5. **Charger les fixtures (données de test)** :
   ```bash
   php bin/console doctrine:fixtures:load
   ```

6. **Démarrer le serveur de développement** :
   ```bash
   symfony server:start
   # ou
   php -S localhost:8000 -t public/
   ```

## Import de données

### Importer des films depuis un fichier CSV

```bash
php bin/console app:import:movies data/movies.csv
```

**Format CSV requis** :
```csv
title,poster_url,year,studio_name,studio_logo_url,director_firstname,director_lastname,actors,tags,added_at
"Iron Man","https://example.com/poster.jpg",2008,"Marvel Studios","https://example.com/logo.png","Jon","Favreau","Robert Downey Jr.|Gwyneth Paltrow","Action|Superhero","2023-01-15 10:30:00"
```

### Importer des studios depuis un fichier CSV

```bash
php bin/console app:import:studios data/studios.csv
```

**Format CSV requis** :
```csv
name,logo_url
"Marvel Studios","https://example.com/logo.png"
```

## Configuration

### Variables d'environnement

#### Popup d'avertissement de contenu adulte

Vous pouvez activer ou désactiver le popup d'avertissement de contenu adulte via le fichier `.env` :

```bash
# Active le popup d'avertissement (valeur par défaut)
ADULT_WARNING_ENABLED=true

# Désactive complètement le popup
ADULT_WARNING_ENABLED=false
```

**Comportement** :
- `true` : Le popup s'affiche à la première visite de chaque utilisateur
- `false` : Le popup ne s'affiche jamais, quelle que soit la situation

**Fonctionnalités du popup** :
- Sélecteur de langue intégré (français, anglais, espagnol)
- Changement de langue en temps réel sans rechargement
- Cookie de 365 jours pour mémoriser l'acceptation
- Redirection vers la langue choisie lors de la confirmation

## Utilisation

### Navigation

- **Accueil** (`/`) : Liste de tous les films triés par date d'ajout
- **Studios** (`/studios`) : Liste de tous les studios
- **Studio détail** (`/studio/{id}`) : Films d'un studio spécifique
- **Réalisateurs** (`/directors`) : Liste de tous les réalisateurs
- **Réalisateur détail** (`/director/{id}`) : Filmographie d'un réalisateur
- **Acteurs** (`/actors`) : Liste de tous les acteurs
- **Acteur détail** (`/actor/{id}`) : Filmographie d'un acteur
- **Film détail** (`/movie/{id}`) : Informations complètes sur un film
- **Recherche** (`/search`) : Recherche globale

### Recherche

La fonction de recherche permet de chercher dans :
- Titres de films
- Noms d'acteurs
- Noms de réalisateurs  
- Noms de studios

### Pagination

Toutes les listes sont paginées :
- Films : 12 par page
- Studios/Réalisateurs/Acteurs : 20 par page

## Technologies utilisées

- **Backend** : Symfony 7.0, PHP 8.1+
- **Base de données** : MySQL/MariaDB avec Doctrine ORM
- **Frontend** : Twig, Bootstrap 5, JavaScript vanilla
- **Pagination** : KnpPaginatorBundle
- **Import** : League CSV

## Structure de la base de données

### Entités principales

- **Movie** : Films avec titre, année, poster, dates
- **Studio** : Studios avec nom et logo
- **Director** : Réalisateurs avec prénom et nom
- **Actor** : Acteurs avec prénom et nom  
- **Tag** : Tags avec nom et couleur

### Relations

- Movie → Studio (ManyToOne)
- Movie → Director (ManyToOne)
- Movie ↔ Actor (ManyToMany)
- Movie ↔ Tag (ManyToMany)

## Développement

### Commandes utiles

```bash
# Créer une nouvelle entité
php bin/console make:entity

# Créer une migration
php bin/console make:migration

# Créer un contrôleur
php bin/console make:controller

# Vider le cache
php bin/console cache:clear

# Lister les routes
php bin/console debug:router
```

### Tests

Les fixtures de test incluent des films populaires avec de vraies données pour tester toutes les fonctionnalités de l'application.

## Licence

MIT License