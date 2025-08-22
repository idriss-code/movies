<?php

namespace App\DataFixtures;

use App\Entity\Actor;
use App\Entity\Director;
use App\Entity\Movie;
use App\Entity\Studio;
use App\Entity\Tag;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // Create Studios
        $studios = $this->createStudios($manager);
        
        // Create Directors
        $directors = $this->createDirectors($manager);
        
        // Create Actors
        $actors = $this->createActors($manager);
        
        // Create Tags
        $tags = $this->createTags($manager);
        
        // Create Movies
        $this->createMovies($manager, $studios, $directors, $actors, $tags);

        $manager->flush();
    }
    
    private function createStudios(ObjectManager $manager): array
    {
        $studiosData = [
            ['Marvel Studios', 'https://upload.wikimedia.org/wikipedia/commons/b/b9/Marvel_Studios_logo.png'],
            ['Walt Disney Pictures', 'https://upload.wikimedia.org/wikipedia/commons/6/61/Disney_logo.svg'],
            ['Warner Bros. Pictures', 'https://upload.wikimedia.org/wikipedia/commons/6/64/Warner_Bros_logo.svg'],
            ['Universal Pictures', 'https://upload.wikimedia.org/wikipedia/commons/4/42/Universal_Pictures_logo.svg'],
            ['Paramount Pictures', 'https://upload.wikimedia.org/wikipedia/commons/3/39/Paramount_Pictures_logo.svg'],
            ['20th Century Studios', 'https://upload.wikimedia.org/wikipedia/commons/4/4f/20th_Century_Studios_logo.svg'],
            ['Sony Pictures', 'https://upload.wikimedia.org/wikipedia/commons/b/be/Sony_Pictures_logo.svg'],
            ['Lucasfilm', 'https://upload.wikimedia.org/wikipedia/commons/9/9d/Lucasfilm_logo.svg'],
        ];
        
        $studios = [];
        foreach ($studiosData as [$name, $logo]) {
            $studio = new Studio();
            $studio->setName($name)
                   ->setLogo($logo);
            $manager->persist($studio);
            $studios[] = $studio;
        }
        
        return $studios;
    }
    
    private function createDirectors(ObjectManager $manager): array
    {
        $directorsData = [
            ['Christopher', 'Nolan'],
            ['Steven', 'Spielberg'],
            ['Martin', 'Scorsese'],
            ['Quentin', 'Tarantino'],
            ['James', 'Cameron'],
            ['Ridley', 'Scott'],
            ['Denis', 'Villeneuve'],
            ['Jordan', 'Peele'],
            ['Greta', 'Gerwig'],
            ['Rian', 'Johnson'],
            ['Jon', 'Favreau'],
            ['Russo', 'Brothers'],
            ['J.J.', 'Abrams'],
            ['George', 'Lucas'],
        ];
        
        $directors = [];
        foreach ($directorsData as [$firstName, $lastName]) {
            $director = new Director();
            $director->setFirstName($firstName)
                     ->setLastName($lastName);
            $manager->persist($director);
            $directors[] = $director;
        }
        
        return $directors;
    }
    
    private function createActors(ObjectManager $manager): array
    {
        $actorsData = [
            ['Robert', 'Downey Jr.'],
            ['Chris', 'Evans'],
            ['Scarlett', 'Johansson'],
            ['Chris', 'Hemsworth'],
            ['Mark', 'Ruffalo'],
            ['Jeremy', 'Renner'],
            ['Tom', 'Holland'],
            ['Benedict', 'Cumberbatch'],
            ['Ryan', 'Gosling'],
            ['Emma', 'Stone'],
            ['Leonardo', 'DiCaprio'],
            ['Brad', 'Pitt'],
            ['Margot', 'Robbie'],
            ['Christian', 'Bale'],
            ['Heath', 'Ledger'],
            ['Joaquin', 'Phoenix'],
            ['Oscar', 'Isaac'],
            ['Adam', 'Driver'],
            ['Timothée', 'Chalamet'],
            ['Saoirse', 'Ronan'],
            ['Lupita', "Nyong'o"],
            ['Michael', 'B. Jordan'],
            ['Brie', 'Larson'],
            ['Tessa', 'Thompson'],
        ];
        
        $actors = [];
        foreach ($actorsData as [$firstName, $lastName]) {
            $actor = new Actor();
            $actor->setFirstName($firstName)
                  ->setLastName($lastName);
            $manager->persist($actor);
            $actors[] = $actor;
        }
        
        return $actors;
    }
    
    private function createTags(ObjectManager $manager): array
    {
        $tagsData = [
            ['Action', '#dc3545'],
            ['Adventure', '#fd7e14'],
            ['Comedy', '#ffc107'],
            ['Drama', '#198754'],
            ['Horror', '#6f42c1'],
            ['Sci-Fi', '#0d6efd'],
            ['Fantasy', '#20c997'],
            ['Thriller', '#6c757d'],
            ['Romance', '#e91e63'],
            ['Animation', '#ff9800'],
            ['Documentary', '#795548'],
            ['Biography', '#607d8b'],
            ['Crime', '#f44336'],
            ['Family', '#4caf50'],
            ['Mystery', '#9c27b0'],
            ['War', '#424242'],
            ['Western', '#8bc34a'],
            ['Musical', '#ff5722'],
            ['Superhero', '#3f51b5'],
            ['Marvel', '#ed1d24'],
        ];
        
        $tags = [];
        foreach ($tagsData as [$name, $color]) {
            $tag = new Tag();
            $tag->setName($name)
                ->setColor($color);
            $manager->persist($tag);
            $tags[] = $tag;
        }
        
        return $tags;
    }
    
    private function createMovies(ObjectManager $manager, array $studios, array $directors, array $actors, array $tags): void
    {
        $moviesData = [
            [
                'title' => 'Avengers: Endgame',
                'year' => 2019,
                'poster' => 'https://image.tmdb.org/t/p/w500/or06FN3Dka5tukK1e9sl16pB3iy.jpg',
                'studio' => 'Marvel Studios',
                'director' => 'Russo Brothers',
                'actors' => ['Robert Downey Jr.', 'Chris Evans', 'Mark Ruffalo', 'Chris Hemsworth', 'Scarlett Johansson'],
                'tags' => ['Action', 'Adventure', 'Superhero', 'Marvel'],
                'download_link' => 'https://example.com/downloads/avengers-endgame.torrent',
                'format' => 'MP4 / 1920x1080',
                'file_size' => '7.2 GB',
                'duration' => '3h 01min'
            ],
            [
                'title' => 'The Dark Knight',
                'year' => 2008,
                'poster' => 'https://image.tmdb.org/t/p/w500/qJ2tW6WMUDux911r6m7haRef0WH.jpg',
                'studio' => 'Warner Bros. Pictures',
                'director' => 'Christopher Nolan',
                'actors' => ['Christian Bale', 'Heath Ledger', 'Aaron Eckhart'],
                'tags' => ['Action', 'Crime', 'Drama'],
                'download_link' => 'https://example.com/downloads/dark-knight.torrent',
                'format' => 'MP4 / 1920x1080',
                'file_size' => '5.8 GB',
                'duration' => '2h 32min'
            ],
            [
                'title' => 'Inception',
                'year' => 2010,
                'poster' => 'https://image.tmdb.org/t/p/w500/9gk7adHYeDvHkCSEqAvQNLV5Uge.jpg',
                'studio' => 'Warner Bros. Pictures',
                'director' => 'Christopher Nolan',
                'actors' => ['Leonardo DiCaprio', 'Marion Cotillard'],
                'tags' => ['Action', 'Sci-Fi', 'Thriller'],
                'download_link' => 'https://example.com/downloads/inception.torrent',
                'format' => 'MP4 / 1920x1080',
                'file_size' => '5.4 GB',
                'duration' => '2h 28min'
            ],
            [
                'title' => 'La La Land',
                'year' => 2016,
                'poster' => 'https://image.tmdb.org/t/p/w500/uDO8zWDhfWwoFdKS4fzkUJt0Rf0.jpg',
                'studio' => 'Summit Entertainment',
                'director' => 'Damien Chazelle',
                'actors' => ['Ryan Gosling', 'Emma Stone'],
                'tags' => ['Romance', 'Musical', 'Drama']
            ],
            [
                'title' => 'Iron Man',
                'year' => 2008,
                'poster' => 'https://image.tmdb.org/t/p/w500/78lPtwv72eTNqFW9COBYI0dWDJa.jpg',
                'studio' => 'Marvel Studios',
                'director' => 'Jon Favreau',
                'actors' => ['Robert Downey Jr.', 'Gwyneth Paltrow'],
                'tags' => ['Action', 'Adventure', 'Superhero', 'Marvel'],
                'download_link' => 'https://example.com/downloads/iron-man.torrent',
                'format' => 'MP4 / 1920x1080',
                'file_size' => '4.2 GB',
                'duration' => '2h 06min'
            ],
        ];
        
        // Add more movies programmatically
        $additionalMovies = [
            'Spider-Man: No Way Home' => ['Marvel Studios', 2021, 'Action'],
            'Avatar' => ['20th Century Studios', 2009, 'Sci-Fi'],
            'Titanic' => ['Paramount Pictures', 1997, 'Romance'],
            'The Lord of the Rings: The Return of the King' => ['Warner Bros. Pictures', 2003, 'Fantasy'],
            'Star Wars: The Force Awakens' => ['Lucasfilm', 2015, 'Sci-Fi'],
            'Jurassic Park' => ['Universal Pictures', 1993, 'Adventure'],
            'The Matrix' => ['Warner Bros. Pictures', 1999, 'Sci-Fi'],
            'Forrest Gump' => ['Paramount Pictures', 1994, 'Drama'],
            'The Shawshank Redemption' => ['Warner Bros. Pictures', 1994, 'Drama'],
            'Pulp Fiction' => ['Miramax Films', 1994, 'Crime'],
        ];
        
        foreach ($moviesData as $movieData) {
            $this->createSingleMovie($manager, $movieData, $studios, $directors, $actors, $tags);
        }
        
        // Create additional movies with random data
        foreach ($additionalMovies as $title => [$studioName, $year, $genre]) {
            $movieData = [
                'title' => $title,
                'year' => $year,
                'studio' => $studioName,
                'director' => $directors[array_rand($directors)]->getFullName(),
                'actors' => array_slice(array_map(fn($a) => $a->getFullName(), $actors), 0, rand(2, 5)),
                'tags' => [$genre, 'Drama']
            ];
            $this->createSingleMovie($manager, $movieData, $studios, $directors, $actors, $tags);
        }
    }
    
    private function createSingleMovie(ObjectManager $manager, array $movieData, array $studios, array $directors, array $actors, array $tags): void
    {
        $movie = new Movie();
        $movie->setTitle($movieData['title'])
              ->setYear($movieData['year'])
              ->setPoster($movieData['poster'] ?? null)
              ->setDownloadLink($movieData['download_link'] ?? null)
              ->setFormat($movieData['format'] ?? null)
              ->setFileSize($movieData['file_size'] ?? null)
              ->setDuration($movieData['duration'] ?? null);
        
        // Set random added date within last 2 years
        $addedDate = new \DateTime();
        $addedDate->modify('-' . rand(0, 730) . ' days');
        $movie->setAddedAt($addedDate);
        
        // Find studio
        if (isset($movieData['studio'])) {
            $studio = array_filter($studios, fn($s) => $s->getName() === $movieData['studio']);
            if ($studio) {
                $movie->setStudio(array_values($studio)[0]);
            }
        }
        
        // Find director
        if (isset($movieData['director'])) {
            if (is_string($movieData['director'])) {
                $director = array_filter($directors, fn($d) => $d->getFullName() === $movieData['director']);
            } else {
                $director = [$movieData['director']];
            }
            if ($director) {
                $movie->setDirector(array_values($director)[0]);
            }
        }
        
        // Add actors
        if (isset($movieData['actors'])) {
            foreach ($movieData['actors'] as $actorName) {
                $actor = array_filter($actors, fn($a) => $a->getFullName() === $actorName);
                if ($actor) {
                    $movie->addActor(array_values($actor)[0]);
                }
            }
        }
        
        // Add tags
        if (isset($movieData['tags'])) {
            foreach ($movieData['tags'] as $tagName) {
                $tag = array_filter($tags, fn($t) => $t->getName() === $tagName);
                if ($tag) {
                    $movie->addTag(array_values($tag)[0]);
                }
            }
        }
        
        $manager->persist($movie);
    }
}