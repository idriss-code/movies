# Movie Management Application - Symfony

## Project Description
Create a Symfony application to manage and display a movie collection with navigation and search functionalities.

## Data Structure

### Movie Entity
- `id` (int, auto-increment, primary key)
- `title` (string, 255)
- `poster` (string, 500) - URL or path to image
- `year` (int)
- `studio` (ManyToOne relation to Studio)
- `director` (ManyToOne relation to Director)
- `actors` (ManyToMany relation to Actor)
- `tags` (ManyToMany relation to Tag)
- `addedAt` (datetime) - date added to platform
- `createdAt` (datetime)
- `updatedAt` (datetime)

### Studio Entity
- `id` (int, auto-increment, primary key)
- `name` (string, 255)
- `logo` (string, 500) - URL or path to studio logo image
- `movies` (OneToMany relation to Movie)

### Director Entity
- `id` (int, auto-increment, primary key)
- `lastName` (string, 255)
- `firstName` (string, 255)
- `movies` (OneToMany relation to Movie)

### Actor Entity
- `id` (int, auto-increment, primary key)
- `lastName` (string, 255)
- `firstName` (string, 255)
- `movies` (ManyToMany relation to Movie)

### Tag Entity
- `id` (int, auto-increment, primary key)
- `name` (string, 100)
- `color` (string, 7) - hexadecimal color code
- `movies` (ManyToMany relation to Movie)

## Required Features

### 1. Homepage (/)
- **Route**: `/`
- **Controller**: `HomeController::index`
- **Template**: `home/index.html.twig`
- **Features**:
  - Display movies as thumbnails (responsive grid)
  - Sort by `addedAt` DESC (most recent first)
  - Pagination (12 movies per page)
  - Each thumbnail shows: poster, title, year, studio
  - Link to movie detail page

### 2. Movie Detail (/movie/{id})
- **Route**: `/movie/{id}`
- **Controller**: `MovieController::show`
- **Template**: `movie/show.html.twig`
- **Features**:
  - Display all movie information
  - List of actors with links to their pages
  - Link to director page
  - Link to studio page
  - Display tags with colors

### 3. Studios List (/studios)
- **Route**: `/studios`
- **Controller**: `StudioController::index`
- **Template**: `studio/index.html.twig`
- **Features**:
  - Paginated list of all studios with logo thumbnails
  - Number of movies per studio
  - Link to each studio detail page

### 4. Studio Detail (/studio/{id})
- **Route**: `/studio/{id}`
- **Controller**: `StudioController::show`
- **Template**: `studio/show.html.twig`
- **Features**:
  - Studio information with logo display
  - List of studio movies (thumbnail format) with pagination (12 movies per page)
  - Sort by `addedAt` DESC

### 5. Directors List (/directors)
- **Route**: `/directors`
- **Controller**: `DirectorController::index`
- **Template**: `director/index.html.twig`
- **Features**:
  - Paginated list of all directors
  - Number of movies per director
  - Link to each director detail page

### 6. Director Detail (/director/{id})
- **Route**: `/director/{id}`
- **Controller**: `DirectorController::show`
- **Template**: `director/show.html.twig`
- **Features**:
  - Director information
  - Filmography (thumbnail format) with pagination (12 movies per page)
  - Sort by `addedAt` DESC

### 7. Actors List (/actors)
- **Route**: `/actors`
- **Controller**: `ActorController::index`
- **Template**: `actor/index.html.twig`
- **Features**:
  - Paginated list of all actors
  - Number of movies per actor
  - Link to each actor detail page

### 8. Actor Detail (/actor/{id})
- **Route**: `/actor/{id}`
- **Controller**: `ActorController::show`
- **Template**: `actor/show.html.twig`
- **Features**:
  - Actor information
  - Filmography (thumbnail format) with pagination (12 movies per page)
  - Sort by `addedAt` DESC

### 9. Search (/search)
- **Route**: `/search`
- **Controller**: `SearchController::index`
- **Template**: `search/index.html.twig`
- **Features**:
  - Search bar present on all pages
  - Search in: movie titles, actor names, director names, studio names
  - Paginated results
  - Optional filters by type (movie, actor, director, studio)

## Technical Structure

### Configuration
- **Symfony Version**: 6.4 or 7.x
- **Database**: MySQL/MariaDB
- **ORM**: Doctrine
- **Template Engine**: Twig
- **Assets**: AssetMapper + minimal vanilla JavaScript
- **Pagination**: KnpPaginatorBundle

### Required Bundles
```composer
composer require symfony/webapp-pack
composer require doctrine/doctrine-bundle
composer require doctrine/doctrine-migrations-bundle
composer require knplabs/knp-paginator-bundle
composer require symfony/form
composer require symfony/validator
composer require symfony/asset-mapper
composer require doctrine/doctrine-fixtures-bundle --dev
composer require league/csv
```

### Data Import System
Create console commands for bulk data import:

#### Import Movies Command
- **Command**: `php bin/console app:import:movies {csv-file}`
- **CSV Format**: title,poster_url,year,studio_name,director_firstname,director_lastname,actors,tags,added_at
- **Features**:
  - Create studios/directors/actors if they don't exist
  - Handle duplicate detection
  - Validate data before import
  - Progress bar display
  - Error logging

#### Import Studios Command
- **Command**: `php bin/console app:import:studios {csv-file}`
- **CSV Format**: name,logo_url
- **Features**:
  - Bulk studio creation with logos
  - Handle duplicates by name
  - Logo URL validation

#### Example CSV Structure (movies.csv):
```csv
title,poster_url,year,studio_name,studio_logo_url,director_firstname,director_lastname,actors,tags,added_at
"Iron Man","https://image.tmdb.org/t/p/w500/78lPtwv72eTNqFW9COBYI0dWDJa.jpg",2008,"Marvel Studios","https://logos.com/marvel.png","Jon","Favre","Robert Downey Jr.|Gwyneth Paltrow|Jeff Bridges","Action|Superhero","2023-01-15 10:30:00"
```

#### Console Commands to Create:
```bash
php bin/console make:command app:import:movies
php bin/console make:command app:import:studios
php bin/console make:command app:import:actors
php bin/console make:command app:import:directors
```

### JavaScript Structure (Minimal)
- **Search functionality**: Simple search form with basic validation
- **Responsive navigation**: Mobile menu toggle
- **Image lazy loading**: For movie posters (optional)
- **No complex frameworks**: Pure vanilla JavaScript only
- **File structure**:
  ```
  assets/
  ├── styles/
  │   └── app.css
  └── scripts/
      ├── app.js (main entry point)
      └── search.js (search functionality)
  ```

### Layout and Design
- **Base Template**: `base.html.twig`
- **Navigation**: Main menu with links to all sections
- **Responsive Design**: Bootstrap 5 (CSS only, minimal JS components)
- **Search Bar**: Simple HTML form with basic JavaScript validation
- **Thumbnail Style**: CSS Grid/Flexbox cards with image, title, year and studio
- **Pagination**: Server-side pagination with Bootstrap styling
- **JavaScript**: Minimal vanilla JS for search and mobile menu only

### Make Commands to Use
```bash
# Create entities
php bin/console make:entity Movie
php bin/console make:entity Studio
php bin/console make:entity Director
php bin/console make:entity Actor
php bin/console make:entity Tag

# Create controllers
php bin/console make:controller HomeController
php bin/console make:controller MovieController
php bin/console make:controller StudioController
php bin/console make:controller DirectorController
php bin/console make:controller ActorController
php bin/console make:controller SearchController

# Migrations
php bin/console make:migration
php bin/console doctrine:migrations:migrate
```

### Fixtures (Optional)
- Create fixtures with test data
- Popular movies with real actors/directors/studios
- At least 50 movies to test pagination
- Include studio logos and movie posters URLs

### Bulk Data Import Features
- **CSV Import**: Command-line tools for importing movies, studios, actors, directors
- **API Integration**: Optional TMDB (The Movie Database) API integration for fetching posters/data
- **Batch Processing**: Handle large datasets (1000+ movies)
- **Data Validation**: Ensure data integrity during import
- **Duplicate Detection**: Prevent duplicate entries
- **Error Handling**: Log import errors and continue processing

### Bonus Features (To implement later)
- Rating/review system
- User favorites
- Advanced sorting (by rating, popularity, etc.)
- Filters by genre/year/studio
- Admin interface to manage movies

## Important Notes
1. Always validate input data
2. Handle error cases (404, etc.)
3. Optimize queries with appropriate joins
4. Responsive and optimized images
5. SEO-friendly URLs
6. Web accessibility (ARIA labels, alt texts)
7. PSR-12 compliant code
8. Unit tests for critical features
9. Minimal JavaScript footprint - prioritize server-side rendering
10. Progressive enhancement approach (works without JS)

## Expected Folder Structure
```
src/
├── Command/
│   ├── ImportMoviesCommand.php
│   ├── ImportStudiosCommand.php
│   ├── ImportActorsCommand.php
│   └── ImportDirectorsCommand.php
├── Controller/
│   ├── HomeController.php
│   ├── MovieController.php
│   ├── StudioController.php
│   ├── DirectorController.php
│   ├── ActorController.php
│   └── SearchController.php
├── Entity/
│   ├── Movie.php
│   ├── Studio.php
│   ├── Director.php
│   ├── Actor.php
│   └── Tag.php
├── Repository/
│   ├── MovieRepository.php
│   ├── StudioRepository.php
│   ├── DirectorRepository.php
│   ├── ActorRepository.php
│   └── TagRepository.php
└── Service/
    └── DataImportService.php

templates/
├── base.html.twig
├── home/
├── movie/
├── studio/
├── director/
├── actor/
└── search/

assets/
├── styles/
│   └── app.css
└── scripts/
    ├── app.js
    └── search.js

data/
├── movies.csv
├── studios.csv
├── actors.csv
└── directors.csv
```
